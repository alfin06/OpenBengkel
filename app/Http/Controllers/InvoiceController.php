<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Part;
use App\Models\InvoiceDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // Show the billing page
    public function create(WorkOrder $workOrder)
    {
        // 1. Load the Work Order with its inventory parts and basic info
        $workOrder->load(['parts', 'items', 'vehicle.customer']);
        $existingInvoice = Invoice::with('details')->where('work_order_id', $workOrder->id)->first();

        // 2. Prepare Internal Spareparts (This comes ONLY from Work Order)
        // These will be displayed in a non-editable table on the invoice page.
        $internalParts = $workOrder->parts->map(function($p) use ($existingInvoice) {
            $savedDetail = $existingInvoice ? $existingInvoice->details->where('part_id', $p->id)->first() : null;

            return [
                'part_id' => $p->id,
                'name'    => $savedDetail ? $savedDetail->item_name : $p->name, // Preference check
                'qty'     => $p->pivot->qty ?? 1,
                'price'   => (int)($savedDetail ? $savedDetail->price : ($p->pivot->price_sold ?? $p->sell_price)),
                'type'    => 'part',
                'discount'=> (int)($savedDetail ? $savedDetail->discount : 0),
                'is_internal' => true,
            ];
        });

        // 3. Prepare Lain-Lain (Editable items: Services, Manual Parts, etc.)
        $existingInvoice = Invoice::with('details')
            ->where('work_order_id', $workOrder->id)
            ->first();

        $lainLain = [];

        if ($existingInvoice) {
            // If draft exists, pull only items that ARE NOT linked to stock (part_id is null)
            $lainLain = $existingInvoice->details->whereNull('part_id')->map(function($detail) {
                return [
                    'name'    => $detail->item_name,
                    'qty'     => $detail->qty,
                    'price'   => (int)$detail->price,
                    'type'    => $detail->type,
                    'discount'=> (int)$detail->discount,
                    'is_internal' => false,
                ];
            });
        } else {
            // Default: Initialize Lain-Lain with tasks (Jasa) from the Work Order
            $lainLain = $workOrder->items->map(function($i) {
                return [
                    'name'    => "JASA: " . $i->task_name,
                    'qty'     => 1,
                    'price'   => 0,
                    'type'    => 'service',
                    'discount'=> 0,
                    'is_internal' => false,
                ];
            });
        }

        return Inertia::render('Invoices/Create', [
            'workOrder'       => $workOrder,
            'shop'            => DB::table('settings')->first(),
            'existingInvoice' => $existingInvoice,
            'internalParts'   => $internalParts ?? [],
            'initialLainLain' => $lainLain ?? [],
        ]);
    }

    // Save the final Invoice (separate from Work Order)
    public function store(Request $request)
    {
        $items = collect($request->items);
        
        // Calculate Totals
        $partsTotal = $items->where('type', 'part')->sum(function($i) {
            return ($i['price'] * $i['qty']) - ($i['discount'] ?? 0);
        });

        $itemsSubtotal = $items->sum(function($i) {
            return ($i['price'] * $i['qty']) - ($i['discount'] ?? 0);
        });
        
        $grandTotal = $itemsSubtotal - ($request->discount ?? 0);

        return DB::transaction(function () use ($request, $partsTotal, $grandTotal) {
            $invoice = Invoice::updateOrCreate(
                ['work_order_id' => $request->work_order_id],
                [
                    'grand_total'    => $grandTotal,
                    'parts_total'    => $partsTotal,
                    'status'         => $request->status,
                    'payment_method' => $request->payment_method,
                    'due_date'       => $request->due_date,
                    'amount_paid'    => ($request->status === 'paid') ? $grandTotal : ($request->amount_paid ?? 0),
                ]
            );

            // --- STEP 1: RETURN STOCK BEFORE DELETING OLD ITEMS ---
            $oldDetails = $invoice->details()->whereNotNull('part_id')->get();
            foreach ($oldDetails as $oldItem) {
                if ($oldItem->part) {
                    $oldItem->part()->increment('stock', $oldItem->qty);
                }
            }

            // Clear old details
            $invoice->details()->delete();

            // --- STEP 2: SAVE CURRENT ITEMS & DEDUCT STOCK ---
            foreach ($request->items as $item) {
                $invoice->details()->create([
                    'part_id'   => $item['part_id'] ?? null,
                    'item_name' => $item['name'],
                    'type'      => $item['type'],
                    'qty'       => $item['qty'],
                    'price'     => $item['price'],
                    'discount'  => $item['discount'] ?? 0,
                    'subtotal'  => ($item['price'] * $item['qty']) - ($item['discount'] ?? 0),
                ]);

                if (!empty($item['part_id'])) {
                    $part = \App\Models\Part::find($item['part_id']);
                    if ($part) {
                        $part->decrement('stock', $item['qty']);
                    }
                }
            }

            // --- STEP 3: CONSTRUCT WHATSAPP URL ---
            $waUrl = null;
            if ($request->action === 'whatsapp') {
                // Reload relationships to get customer and vehicle data
                $invoice->load('workOrder.vehicle.customer');
                $customer = $invoice->workOrder->vehicle->customer;
                $plate = $invoice->workOrder->vehicle->plate_number;
                
                // Clean phone number: Convert 08... to 628...
                $phone = $customer->phone_1;
                $phone = preg_replace('/^0/', '62', $phone);
                
                // Construct Message
                $message = "Halo *" . ($customer->name ?? 'Pelanggan') . "*,\n\n" .
                        "Terima kasih telah servis di *" . (config('app.name') ?? 'Bengkel Kami') . "*.\n" .
                        "Berikut adalah rincian nota untuk kendaraan *" . $plate . "*:\n\n" .
                        "Total: *Rp " . number_format($grandTotal, 0, ',', '.') . "*\n" .
                        "Status: *" . strtoupper($invoice->status) . "*\n\n" .
                        "Detail nota dapat dilihat pada link berikut:\n" . route('invoices.show', $invoice->uuid);

                $waUrl = "https://wa.me/" . $phone . "?text=" . urlencode($message);
            }

            // 4. Update Work Order Status
            if ($request->status !== 'draft') {
                WorkOrder::where('id', $request->work_order_id)->update(['status' => 'Selesai']);
            } else {
                WorkOrder::where('id', $request->work_order_id)->update(['status' => 'Proses']);
            }

            $msg = $request->status === 'draft' ? 'Draft Invoice Tersimpan!' : 'Invoice Berhasil Diselesaikan!';
            
            // Pass the wa_url back via flash data
            return redirect()->back()->with([
                'success' => $msg,
                'wa_url' => $waUrl
            ]);
        });
    }

    // 1. Fetch the data for the Receivables page
    public function receivables()
    {
        $invoices = Invoice::with(['workOrder.vehicle.customer'])
            ->where('status', 'unpaid')
            ->orderBy('due_date', 'asc')
            ->get();

        return inertia('Invoices/Receivables', [
            'invoices' => $invoices
        ]);
    }

    // 2. Update the status when the company settles the bill
    public function settle(Request $request, Invoice $invoice)
    {
        $invoice->update([
            'status' => 'paid',
            'amount_paid' => $invoice->grand_total, // Bumps the paid amount to 100%
            'payment_method' => $request->payment_method ?? 'Transfer',
        ]);

        return redirect()->back()->with('success', 'Receivable for Invoice #' . $invoice->id . ' has been settled!');
    }

    public function showPublic(Invoice $invoice)
    {
        // Important: We need all these relationships for the Vue page to work!
        $invoice->load(['details', 'workOrder.vehicle.customer']);
        
        return Inertia::render('Invoices/PublicView', [
            'invoice' => $invoice,
            'shop' => \DB::table('settings')->first(),
        ]);
    }
}
