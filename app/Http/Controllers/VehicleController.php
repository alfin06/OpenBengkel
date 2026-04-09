<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|unique:vehicles,plate_number',
            'brand' => 'required|string',
            'model' => 'required|string',
            'color' => 'nullable|string',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'chassis_number' => 'nullable|string',
            'engine_number' => 'nullable|string',
        ]);

        $customer->vehicles()->create($validated);

        return redirect()->back()->with('success', 'Kendaraan berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    // public function show(WorkOrder $workOrder)
    // {
    //     return Inertia::render('WorkOrders/Show', [
    //         'workOrder' => $workOrder->load([
    //             'vehicle.customer', // Now this will work!
    //             'items', 
    //             'parts.part'
    //         ])
    //     ]);
    // }

    /**
     * Display the specified resource (Vehicle Medical Record).
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load([
            'customer', 
            'workOrders' => function($q) {
                $q->with([
                    'mechanic', 
                    'items',
                    'invoice.details'
                ])->latest();
            }
        ]);

        return Inertia::render('Vehicles/Show', ['vehicle' => $vehicle]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|unique:vehicles,plate_number,' . $vehicle->id,
            'brand' => 'required|string',
            'model' => 'required|string',
            'color' => 'nullable|string',
            'year' => 'nullable|integer',
            'chassis_number' => 'nullable|string',
            'engine_number' => 'nullable|string',
        ]);

        $vehicle->update($validated);

        return redirect()->back()->with('success', 'Data mobil berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        // Safety Check: If there are Work Orders for this car, you might want to block deletion
        // or use SoftDeletes. For now, we'll do a straight delete.
        $vehicle->delete();

        return redirect()->back()->with('success', 'Mobil telah dihapus dari database.');
    }
}
