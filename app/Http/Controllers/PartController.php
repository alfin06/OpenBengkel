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
            ->where('stock', '>', -100)
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
        $part->delete();

        return redirect()->back()->with('success', 'Barang berhasil dihapus dari inventaris.');
    }

    public function show(Part $part)
    {
        // 1. SALES HISTORY (Items going OUT)
        $salesTransactions = InvoiceDetail::where('part_id', $part->id)
            ->with(['invoice.workOrder.vehicle.customer'])
            ->latest()
            ->get()
            ->map(function($t) {
                return [
                    'date' => $t->created_at->format('d/m/Y'),
                    'invoice_id' => $t->invoice_id,
                    'invoice_uuid' => $t->invoice->uuid,
                    'customer' => $t->invoice->workOrder->vehicle->customer->name ?? 'Umum',
                    'plate_number' => $t->invoice->workOrder->vehicle->plate_number ?? '-',
                    'qty' => $t->qty,
                    'price' => (int)$t->price,
                ];
            });

        // 2. PURCHASE HISTORY (Items coming IN)
        // We fetch from the new StockEntryItem model we created
        $purchaseHistory = \App\Models\StockEntryItem::where('part_id', $part->id)
            ->with(['stockEntry.supplier'])
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->stockEntry->entry_date->format('d/m/Y'),
                    'supplier_name' => $item->stockEntry->supplier->name,
                    'qty' => $item->qty,
                    'price' => (int)$item->purchase_price,
                    'reference' => $item->stockEntry->reference_number,
                ];
            });

        // 3. ANALYTICS (Cheapest vs Last)
        $cheapest = $purchaseHistory->sortBy('price')->first();

        return Inertia::render('Parts/Show', [
            'part' => $part,
            'transactions' => $salesTransactions, // Sales data
            'purchaseHistory' => $purchaseHistory, // Purchase data
            'stats' => [
                'cheapest_price' => $cheapest['price'] ?? 0,
                'cheapest_supplier' => $cheapest['supplier_name'] ?? 'N/A',
                'last_purchase_price' => $purchaseHistory->first()['price'] ?? 0,
            ]
        ]);
    }

    public function quickStore(Request $request)
    {

        // If SKU is provided, check if it already exists
        if ($request->sku) {
            $existingPart = Part::where('sku', $request->sku)->first();
            if ($existingPart) {
                return response()->json($existingPart); // Just return the existing one!
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:parts,sku',
            'type' => 'required|in:Part,Service',
            'buy_price' => 'nullable|numeric',
            'sell_price' => 'nullable|numeric',
        ], [
            'sku.unique' => 'Kode SKU/Barcode ini sudah digunakan oleh barang lain.'
        ]);

        $part = Part::create([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'type' => $validated['type'],
            'buy_price' => $validated['buy_price'] ?? 0,
            'sell_price' => $validated['sell_price'] ?? 0,
            'stock' => 0,
        ]);

        return response()->json($part);
    }
}