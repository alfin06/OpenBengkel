<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;
use Inertia\Inertia;

use \App\Models\InvoiceDetail;

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

    public function update(Request $request, Part $part)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100|unique:parts,sku,' . $part->id,
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'discount_percent' => 'nullable|integer|min:0|max:100',
        ]);

        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;
        $validated['min_stock'] = $validated['min_stock'] ?? 5;

        $part->update($validated);

        return redirect()->back()->with('success', 'Data stok barang berhasil diperbarui!');
    }

    public function destroy(Part $part)
    {
        $part->delete(); // This now soft-deletes automatically

        return redirect()->back()->with('success', 'Barang berhasil dihapus dari inventaris.');
    }

    public function show(Part $part)
    {
        // We load the transactions where this part was sold
        $transactions = InvoiceDetail::where('part_id', $part->id)
            ->with(['invoice.workOrder.vehicle.customer'])
            ->latest()
            ->get();

        return Inertia::render('Parts/Show', [
            'part' => $part,
            'transactions' => $transactions->map(function($t) {
                return [
                    'date' => $t->created_at->format('d/m/Y'),
                    'invoice_id' => $t->invoice_id,
                    'invoice_uuid' => $t->invoice->uuid,
                    'customer' => $t->invoice->workOrder->vehicle->customer->name ?? 'Umum',
                    'plate_number' => $t->invoice->workOrder->vehicle->plate_number ?? '-',
                    'qty' => $t->qty,
                    'price' => (int)$t->price,
                ];
            })
        ]);
    }
}