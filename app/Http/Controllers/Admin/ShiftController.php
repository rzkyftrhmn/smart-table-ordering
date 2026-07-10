<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $shifts = Shift::withCount('users')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('admin.shifts.index', compact('shifts', 'search'));
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
        ]);

        Shift::create($validated);

        return redirect()
            ->route('shifts.index')
            ->with('success', 'Shift berhasil ditambahkan.');
    }

    public function show(Shift $shift)
    {
        return redirect()->route('shifts.edit', $shift);
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
        ]);

        $shift->update($validated);

        return redirect()
            ->route('shifts.index')
            ->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy(Shift $shift)
    {
        if ($shift->users()->exists()) {
            return back()->with('error', 'Shift ini masih digunakan oleh staf.');
        }

        $shift->delete();

        return redirect()
            ->route('shifts.index')
            ->with('success', 'Shift berhasil dihapus.');
    }
}
