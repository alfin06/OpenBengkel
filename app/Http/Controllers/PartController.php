<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartController extends Controller
{
    public function index()
    {
        return Inertia::render('Parts/Index', [
            'parts' => Part::orderBy('name')->get()
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
            // We send all parts so the Vue page can "find" the one being zapped
            'parts' => Part::orderBy('name')->get()
        ]);
    }
}