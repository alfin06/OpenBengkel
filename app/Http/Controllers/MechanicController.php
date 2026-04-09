<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
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
            'nik' => 'nullable|digits:16',
            'is_active' => 'required|boolean',
        ], [
            'nik.digits' => 'Nomor NIK harus tepat 16 digit angka.',
            'name.required' => 'Nama mekanik tidak boleh kosong.',
        ]);

        Mechanic::create($validated);

        return redirect()->back()->with('success', 'Mekanik berhasil ditambahkan!');
    }

    public function update(Request $request, Mechanic $mechanic)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'nik' => 'nullable|string|digits:16',
            'ktp_file' => 'nullable|image|max:2048',
        ], [
            'nik.digits' => 'Nomor NIK harus tepat 16 digit angka.',
            'name.required' => 'Nama mekanik tidak boleh kosong.',
        ]);

        if ($request->hasFile('ktp_file') && auth()->user()->role === 'admin') {
            // Delete old KTP if exists
            if ($mechanic->ktp_path) {
                Storage::disk('public')->delete($mechanic->ktp_path);
            }
            $path = $request->file('ktp_file')->store('ktp_mechanics', 'public');
            $mechanic->ktp_path = $path;
        }

        $mechanic->update($validated);
        return redirect()->back();
    }

    public function destroy(Mechanic $mechanic)
    {
        $mechanic->delete(); // This safely soft-deletes them
        return redirect()->back()->with('success', 'Mekanik dinonaktifkan/dihapus.');
    }
}