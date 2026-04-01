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
    public function store(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        // 1. Create the Invoice Header
        $invoice = Invoice::create([
            'work_order_id' => $workOrder->id,
            'total_amount' => collect($request->items)->sum('price'),
            'payment_status' => 'Unpaid',
        ]);

        // 2. Save the specific Invoice Items (Independent of Work Order)
        foreach ($request->items as $item) {
            $invoice->items()->create($item);
        }

        return redirect()->route('customers.show', $workOrder->vehicle->customer_id)
            ->with('success', 'Invoice berhasil dibuat!');
    }
}
