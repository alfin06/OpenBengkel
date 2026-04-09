<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // Show the billing page
    public function create(WorkOrder $workOrder)
    {
        // Look for an existing invoice (draft or otherwise)
        $existingInvoice = Invoice::with('details')
            ->where('work_order_id', $workOrder->id)
            ->first();

        // Prepare saved items for the Vue component
        $savedItems = null;
        if ($existingInvoice) {
            $savedItems = $existingInvoice->details->map(function($detail) {
                return [
                    'name' => $detail->item_name,
                    'qty' => $detail->qty,
                    'price' => $detail->price,
                    'type' => $detail->type,
                    'discount' => $detail->discount,
                ];
            });
        }

        return Inertia::render('Invoices/Create', [
            'workOrder' => $workOrder->load('items', 'vehicle.customer'),
            'shop' => DB::table('settings')->first(),
            'existingInvoice' => $existingInvoice,
            'savedItems' => $savedItems,
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
            // 1. Find existing invoice for this WO (if any) or create new
            $invoice = Invoice::updateOrCreate(
                ['work_order_id' => $request->work_order_id],
                [
                    'grand_total'    => $grandTotal,
                    'parts_total'    => $partsTotal,
                    'status'         => $request->status ?? 'paid', 
                    'payment_method' => $request->payment_method,
                    'due_date'       => $request->due_date,
                    'amount_paid'    => ($request->status === 'paid') ? $grandTotal : ($request->amount_paid ?? 0),
                ]
            );

            // 2. Clear old details to prevent duplicates on re-save
            $invoice->details()->delete();

            // 3. Save current items
            foreach ($request->items as $item) {
                $invoice->details()->create([
                    'item_name' => $item['name'],
                    'type'      => $item['type'],
                    'qty'       => $item['qty'],
                    'price'     => $item['price'],
                    'discount'  => $item['discount'] ?? 0,
                    'subtotal'  => ($item['price'] * $item['qty']) - ($item['discount'] ?? 0),
                ]);
            }

            // 4. Update Work Order Status ONLY if not a draft
            if ($request->status !== 'draft') {
                WorkOrder::where('id', $request->work_order_id)->update(['status' => 'Selesai']);
            } else {
                // Keep status as 'Proses' so it stays visible in the active list
                WorkOrder::where('id', $request->work_order_id)->update(['status' => 'Proses']);
            }

            $msg = $request->status === 'draft' ? 'Draft Invoice Tersimpan!' : 'Invoice Berhasil Diselesaikan!';
            return redirect()->back()->with('success', $msg);
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
}
