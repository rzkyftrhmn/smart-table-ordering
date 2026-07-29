<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $users = User::with('shift')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('Admin.users.index', compact('users', 'search'));
    }

    public function create()
    {
        $shifts = Shift::orderBy('start_time')->get();

        return view('Admin.users.create', compact('shifts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'kasir', 'dapur', 'owner'])],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['username'] = $this->uniqueUsername($validated['name']);
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        return redirect()->route('users.edit', $user);
    }

    public function edit(User $user)
    {
        $shifts = Shift::orderBy('start_time')->get();

        return view('Admin.users.edit', compact('user', 'shifts'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'confirmed', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'kasir', 'dapur', 'owner'])],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['username'] = $this->uniqueUsername($validated['name'], $user->id);
        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Akun yang sedang digunakan tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    private function uniqueUsername(string $name, ?int $ignoreUserId = null): string
    {
        $base = Str::of($name)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->limit(80, '')
            ->toString();

        if ($base === '') {
            $base = 'user';
        }

        $username = $base;
        $counter = 2;

        while (
            User::where('username', $username)
                ->when($ignoreUserId, fn ($query) => $query->where('id', '!=', $ignoreUserId))
                ->exists()
        ) {
            $suffix = '_' . $counter;
            $username = Str::limit($base, 100 - strlen($suffix), '') . $suffix;
            $counter++;
        }

        return $username;
    }
}
