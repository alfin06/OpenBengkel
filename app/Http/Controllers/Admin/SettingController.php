<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Setting; // Or App\Models\Shop

class SettingController extends Controller
{
    public function edit()
    {
        // Fetch the first row of settings, or create a blank one if the table is empty
        $setting = Setting::first() ?? new Setting();

        return Inertia::render('Settings/Edit', [
            'setting' => $setting
        ]);
    }

    public function update(Request $request)
    {
        $setting = Setting::first() ?? new Setting();

        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_phone' => 'nullable|string|max:50',
            'shop_address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Max 2MB
        ]);

        // Handle the Logo Upload
        if ($request->hasFile('logo')) {
            // Delete the old logo if it exists
            if ($setting->logo_path) {
                // Extract just the filename from the path to delete it safely
                $oldPath = str_replace('/storage/', 'public/', $setting->logo_path);
                Storage::delete($oldPath);
            }

            // Save the new logo and generate the public URL
            $path = $request->file('logo')->store('public/logos');
            $setting->logo_path = Storage::url($path);
        }

        // Update text fields
        $setting->shop_name = $validated['shop_name'];
        $setting->shop_phone = $validated['shop_phone'];
        $setting->shop_address = $validated['shop_address'];
        $setting->save();

        return redirect()->back()->with('success', 'Profil bengkel berhasil diperbarui!');
    }
}