<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Suppliers/Index', [
            'suppliers' => Supplier::query()
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sales_name', 'like', "%{$search}%")
                        ->orWhere('phone_whatsapp', 'like', "%{$search}%");
                })
                ->orderBy('name', 'asc')
                ->latest()
                ->get(),
            'filters' => $request->only(['search']) // Pass back to sync the input field
        ]);
    }

    public function store(Request $request)
    {
        $valid = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'nullable|string',
            'phone_office'   => 'nullable|string',
            'phone_whatsapp' => 'nullable|string',
            'phone_sales'    => 'nullable|string',
            'sales_name'     => 'nullable|string',
            'email'          => 'nullable|email',
            'npwp'           => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        Supplier::create($valid);
        return redirect()->back()->with('success', 'Supplier berhasil ditambahkan!');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $valid = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'nullable|string',
            'phone_office'   => 'nullable|string',
            'phone_whatsapp' => 'nullable|string',
            'phone_sales'    => 'nullable|string',
            'sales_name'     => 'nullable|string',
            'email'          => 'nullable|email',
            'npwp'           => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        $supplier->update($valid);
        return redirect()->back()->with('success', 'Data supplier diperbarui!');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->back()->with('success', 'Supplier telah dihapus.');
    }

    public function show(Supplier $supplier)
    {
        $purchaseHistory = $supplier->stockEntries()
            ->with(['items.part']) // Load the items and part names
            ->latest()
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'date' => $entry->entry_date->format('d/m/Y'),
                    'reference' => $entry->reference_number,
                    'total_cost' => (int)$entry->total_cost,
                    'item_count' => $entry->items->count(),
                    'summary' => $entry->items->take(3)->map(function($i) {
                        return $i->part->name;
                    })->implode(', ') . ($entry->items->count() > 3 ? '...' : ''),
                ];
            });

        return Inertia::render('Suppliers/Show', [
            'supplier' => $supplier,
            'purchaseHistory' => $purchaseHistory,
            'totalSpent' => (int)$purchaseHistory->sum('total_cost'),
        ]);
    }
}