<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Parts/Index', [
            'parts' => Part::query()
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                })
                ->orderBy('name', 'asc') // Alphabetical sorting (A-Z)
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:parts,sku',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'type' => 'required|in:Part,Service',
        ]);

        Part::create($validated);
        return redirect()->back()->with('success', 'Item berhasil ditambah!');
    }

    public function restock(Request $request, Part $part)
    {
        $validated = $request->validate([
            'incoming_qty' => 'required|integer|min:1',
            'new_buy_price' => 'required|numeric|min:0',
        ]);

        // WAC (Weighted Average Cost) Logic
        $currentTotalValue = $part->stock * $part->buy_price;
        $incomingValue = $validated['incoming_qty'] * $validated['new_buy_price'];
        $newTotalQty = $part->stock + $validated['incoming_qty'];

        $newAverageBuyPrice = ($currentTotalValue + $incomingValue) / $newTotalQty;

        $part->update([
            'stock' => $newTotalQty,
            'buy_price' => $newAverageBuyPrice,
        ]);

        return redirect()->back()->with('success', "Stok {$part->name} berhasil ditambah!");
    }

    public function restockPage()
    {
        return Inertia::render('Parts/Restock', [
            // Only send 'Part' type items to this page
            'parts' => Part::where('type', 'Part')
                ->orderBy('name')
                ->get()
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        return Part::where('name', 'LIKE', "%{$query}%")
            ->orWhere('sku', 'LIKE', "%{$query}%")
            ->where('stock', '>', -100) // Allow slightly negative if you allow pre-orders
            ->limit(10)
            ->get(['id', 'name', 'sku', 'sell_price', 'stock']);
    }
}