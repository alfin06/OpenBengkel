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
        return Inertia::render('Invoices/Create', [
            'workOrder' => $workOrder->load('items', 'vehicle.customer'),
        ]);
    }

    // Save the final Invoice (separate from Work Order)
    public function store(Request $request)
    {
        // 1. Calculate Totals from the request
        $items = collect($request->items);
        $partsTotal = $items->where('type', 'part')->sum(fn($i) => $i['price'] * $i['qty']);
        $grandTotal = $items->sum(fn($i) => $i['price'] * $i['qty']) - $request->discount;

        // 2. Wrap in a Transaction for safety
        return DB::transaction(function () use ($request, $partsTotal, $grandTotal) {
            $invoice = Invoice::create([
                'work_order_id'  => $request->work_order_id,
                'grand_total'    => $grandTotal,
                'parts_total'    => $partsTotal,
                'status'         => $request->status ?? 'paid', 
                'payment_method' => $request->payment_method,
                'due_date'       => $request->due_date,
                'amount_paid'    => ($request->status === 'paid') ? $grandTotal : ($request->amount_paid ?? 0),
            ]);

            foreach ($request->items as $item) {
                $invoice->details()->create([
                    'item_name' => $item['name'],
                    'qty'       => $item['qty'],
                    'price'     => $item['price'],
                    'subtotal'  => $item['price'] * $item['qty'],
                ]);
            }

            // Mark WO as completed
            WorkOrder::where('id', $request->work_order_id)->update(['status' => 'Selesai']);

            // IMPORTANT: We stay on the same page!
            return redirect()->back()->with('success', 'Invoice Berhasil Disimpan!');
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
