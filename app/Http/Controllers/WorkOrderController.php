<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Part;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Inertia\Response;

use App\Models\WorkOrder;

class WorkOrderController extends Controller
{
    public function create(Request $request)
    {
        $search = $request->input('plate');
        $vehicle = null;

        if ($search) {
            $vehicle = Vehicle::where('plate_number', 'like', "%{$search}%")
                ->with('customer')
                ->first();
        }

        return Inertia::render('WorkOrders/CheckIn', [
            'foundVehicle' => $vehicle,
            'filters' => $request->only(['plate'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'  => 'required|exists:vehicles,id',
            'driver_name' => 'nullable|string|max:255',
            'odometer' => 'nullable|numeric',
            'complaints'  => 'nullable|string',
        ]);

        // Simpan ke database
        $workOrder = WorkOrder::create([
            'vehicle_id'  => $validated['vehicle_id'],
            'driver_name' => $validated['driver_name'],
            'odometer' => (string) $validated['odometer'],
            'complaints'  => $validated['complaints'],
            'status'      => 'Pending',
        ]);

        return redirect()->route('work-orders.show', $workOrder->id)
            ->with('success', 'Work Order berhasil dibuat!');
    }

    public function show(WorkOrder $workOrder) {
        return Inertia::render('WorkOrders/Show', [
            'workOrder' => $workOrder->load(['vehicle', 'items', 'parts.part'])
        ]);
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'driver_name' => 'nullable|string|max:255',
            'odometer' => 'nullable|numeric',
            'complaints' => 'nullable|string',
        ]);

        $workOrder->update($validated);

        return redirect()->back()->with('success', 'Info Check-in berhasil diperbarui!');
    }

    public function startWork(WorkOrder $workOrder)
    {
        $workOrder->update(['status' => 'Proses']);

        return redirect()->back()->with('success', 'Pengerjaan dimulai! Status: Proses.');
    }

    public function addPart(Request $request, WorkOrder $workOrder)
    {
        $request->validate(['sku' => 'required|string']);
        
        $part = Part::where('sku', $request->sku)->first();

        if (!$part) {
            return back()->with('error', 'Barang tidak ditemukan!');
        }

        $workOrder->parts()->create([
            'part_id' => $part->id,
            'qty' => 1,
            'price_sold' => $part->sell_price,
            'cost_at_sale' => $part->buy_price,
        ]);

        $part->decrement('stock', 1);

        return back()->with('success', "{$part->name} ditambahkan!");
    }

    public function searchParts(Request $request)
    {
        $query = $request->get('q');
        // Search by Name or SKU
        return Part::where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%")
                    ->limit(10)
                    ->get(['id', 'name', 'sku', 'sell_price', 'stock']);
    }

    public function createInvoice(WorkOrder $workOrder)
    {
        $workOrder->load(['vehicle.customer', 'items', 'parts.part']);

        // Let the database multiply and sum in one go
        $totalParts = $workOrder->parts()
            ->selectRaw('SUM(price_sold * qty) as total')
            ->value('total') ?? 0;

        return Inertia::render('Invoices/Create', [
            'workOrder' => $workOrder,
            'totalParts' => (float) $totalParts
        ]);
    }
}
