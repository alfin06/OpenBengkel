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
}