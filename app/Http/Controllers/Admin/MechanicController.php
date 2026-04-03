<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mechanic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MechanicController extends Controller
{
    public function index()
    {
        $mechanics = Mechanic::withCount('workOrders')
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('Mechanics/Index', [
            'mechanics' => $mechanics
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ]);

        Mechanic::create($validated);

        return redirect()->back()->with('success', 'Mekanik berhasil ditambahkan!');
    }

    public function update(Request $request, Mechanic $mechanic)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ]);

        $mechanic->update($validated);

        return redirect()->back()->with('success', 'Data mekanik diperbarui!');
    }

    public function destroy(Mechanic $mechanic)
    {
        $mechanic->delete(); // This safely soft-deletes them
        return redirect()->back()->with('success', 'Mekanik dinonaktifkan/dihapus.');
    }
}