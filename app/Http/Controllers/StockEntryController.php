<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Supplier;
use App\Models\StockEntry;
use App\Models\StockEntryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockEntryController extends Controller
{
    public function create()
    {
        return Inertia::render('StockEntries/Create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'parts' => Part::where('type', 'Part')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'entry_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $entry = StockEntry::create([
                'supplier_id' => $request->supplier_id,
                'entry_date' => $request->entry_date,
                'reference_number' => $request->reference_number,
                'total_cost' => $request->total_cost,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $entry->items()->create($item);

                // Update physical stock
                $part = Part::find($item['part_id']);
                $part->increment('stock', $item['qty']);

                // Sync the relationship with the latest purchase price
                $part->suppliers()->syncWithoutDetaching([
                    $request->supplier_id => ['purchase_price' => $item['purchase_price']]
                ]);
            }
        });

        return redirect()->route('parts.index')->with('success', 'Stok berhasil diperbarui!');
    }

    public function show(StockEntry $stockEntry)
    {
        // Load the items and the parts associated with this specific entry
        $stockEntry->load(['items.part', 'supplier']);

        return Inertia::render('StockEntries/Show', [
            'entry' => [
                'id' => $stockEntry->id,
                'reference' => $stockEntry->reference_number,
                'date' => $stockEntry->entry_date->format('d/m/Y'),
                'supplier_name' => $stockEntry->supplier->name,
                'notes' => $stockEntry->notes,
                'total_cost' => (int)$stockEntry->total_cost,
                'items' => $stockEntry->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'part_name' => $item->part->name,
                        'sku' => $item->part->sku,
                        'qty' => $item->qty,
                        'price' => (int)$item->purchase_price,
                        'subtotal' => (int)($item->qty * $item->purchase_price),
                    ];
                })
            ]
        ]);
    }
}