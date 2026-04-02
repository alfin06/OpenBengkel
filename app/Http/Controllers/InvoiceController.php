<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        $validated = $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'labor_fee' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        $wo = WorkOrder::find($validated['work_order_id']);
        
        // Calculate final grand total on the server (Security First!)
        $partsTotal = $wo->parts_total; 
        $grandTotal = ($partsTotal + $validated['labor_fee']) - $validated['discount'];

        // 1. Create the Invoice
        $invoice = Invoice::create(array_merge($validated, [
            'parts_total' => $partsTotal,
            'grand_total' => $grandTotal,
        ]));

        // 2. Mark Work Order as 'Selesai'
        $wo->update(['status' => 'Selesai']);

        return redirect()->route('work-orders.index')
            ->with('success', 'Pembayaran Berhasil! Job #' . $wo->id . ' ditutup.');
    }
}
