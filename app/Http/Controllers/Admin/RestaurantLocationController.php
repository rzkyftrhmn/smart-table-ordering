<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantLocation;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RestaurantLocationController extends Controller
{
    /**
     * Tampilkan form setting lokasi resto.
     * Karena cuma 1 row, langsung load row current (atau default kalau belum ada).
     */
    public function edit(): View
    {
        $location = RestaurantLocation::current();

        return view('admin.restaurant-location.edit', compact('location'));
    }

    /**
     * Simpan/update lokasi resto.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
        ]);

        $location = RestaurantLocation::current();
        $location->update($validated);

        return redirect()
            ->route('admin.restaurant-location.edit')
            ->with('success', 'Lokasi restaurant berhasil diperbarui.');
    }
}