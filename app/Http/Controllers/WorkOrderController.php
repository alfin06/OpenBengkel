<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Inertia\Inertia;
use Inertia\Response;

use App\Models\WorkOrder;
use App\Models\WorkOrderPart;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Mechanic;

class WorkOrderController extends Controller
{
    public function create(Request $request)
    {
        $foundVehicle = null;

        // If coming from the search bar (plate typing)
        if ($request->filled('plate')) {
            $foundVehicle = Vehicle::with('customer')
                ->where('plate_number', 'LIKE', '%' . $request->plate . '%')
                ->first();
        } 
        // If coming from the Customer/History page Check-In button
        elseif ($request->filled('vehicle_id')) {
            $foundVehicle = Vehicle::with('customer')
                ->find($request->vehicle_id);
        }

        return inertia('WorkOrders/CheckIn', [
            'foundVehicle' => $foundVehicle,
            'filters' => $request->only('plate', 'vehicle_id'),
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

    public function show(WorkOrder $workOrder)
    {
        return inertia('WorkOrders/Show', [
            'workOrder' => $workOrder->load([
                'vehicle.customer', 
                'items', 
                'parts.part',
                'mechanic'
            ]),
            // Fetch all active mechanics to populate the dropdown
            'mechanics' => Mechanic::where('is_active', true)->get() 
        ]);
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'driver_name' => 'nullable|string|max:255',
            'odometer' => 'nullable|numeric',
            'complaints' => 'nullable|string',
            'mechanic_id' => 'nullable|exists:mechanics,id',
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

    public function createInvoice($id)
    {
        // 1. Get the Work Order with basic info
        $workOrder = WorkOrder::with(['vehicle.customer', 'items'])->findOrFail($id);

        $shop = DB::table('settings')->first();

        // 2. Get the Parts directly from the database to be 100% sure
        $formattedParts = WorkOrderPart::where('work_order_id', $id)
            ->with('Part')
            ->get()
            ->map(function ($p) {
                return [
                    'name' => $p->part ? $p->part->name : 'Barang #' . $p->part_id,
                    'qty' => (int) $p->qty,
                    'price' => (float) $p->price_sold,
                    'type' => 'part'
                ];
            });

        return Inertia::render('Invoices/Create', [
            'workOrder' => $workOrder,
            'formattedParts' => $formattedParts,
            'shop' => $shop,
            'totalParts' => (float) $workOrder->parts_total,
        ]);
    }

    public function storeUnified(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string',
            'customer_name' => 'required|string',
            'phone_1' => 'nullable|digits_between:10,15',
            'brand' => 'required|string',
            'model' => 'required|string',
            'odometer' => 'nullable|numeric',
        ], [
            'plate_number.required' => 'Nomor plat harus diisi.',
            'customer_name.required' => 'Nama pelanggan harus diisi.',
            'phone_1.digits_between' => 'Nomor WhatsApp harus berupa angka (10-15 digit).',
            'brand.required' => 'Merek mobil harus diisi.',
            'model.required' => 'Model mobil harus diisi.',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Handle Customer (Find by phone or create)
            $customer = Customer::updateOrCreate(
                ['phone_1' => $request->phone_1],
                ['name' => $request->customer_name, 'address' => $request->address]
            );

            // 2. Handle Vehicle (Find by plate or create)
            $vehicle = Vehicle::updateOrCreate(
                ['plate_number' => strtoupper($request->plate_number)],
                [
                    'customer_id' => $customer->id,
                    'brand' => $request->brand,
                    'model' => $request->model,
                    'color' => $request->color,
                    'year' => $request->year,
                ]
            );

            // 3. Create Work Order
            $workOrder = WorkOrder::create([
                'vehicle_id' => $vehicle->id,
                'driver_name' => $request->driver_name ?? $customer->name,
                'odometer' => $request->odometer,
                'complaints' => $request->complaints,
                'status' => 'Pending',
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('work-orders.show', $workOrder->id)
                            ->with('success', 'Check-in berhasil!');
        });
    }
}
