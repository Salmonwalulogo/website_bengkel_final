<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('backend.admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('backend.admin.users.create');
    }

    public function show(User $user)
    {
        return view('backend.admin.users.show', compact('user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'username' => 'required|string|max:255|alpha_dash|unique:users,username',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,kasir,customer',
            'no_hp' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        unset($validated['photo']);
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('users', 'public');
        }
        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('backend.admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'username' => 'required|string|max:255|alpha_dash|unique:users,username,' . $user->id,
            'role' => 'required|in:admin,kasir,customer',
            'no_hp' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $validated['photo'] = $request->file('photo')->store('users', 'public');
        } else {
            unset($validated['photo']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function updateStatus(Request $request, User $user)
    {
        abort_unless($user->isCustomer(), 404);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);
        $isActive = (bool) $validated['is_active'];
        $user->update(['is_active' => $isActive]);

        if (! $isActive) {
            $user->tokens()->delete();
        }

        return redirect()->route('admin.users.index')->with(
            'success',
            $isActive
                ? 'Akun pelanggan berhasil diaktifkan.'
                : 'Akun pelanggan berhasil dinonaktifkan. Akses web dan token API telah diblokir.'
        );
    }

    public function destroy(User $user)
    {
        abort_if($user->is(auth()->user()), 422, 'Akun yang sedang digunakan tidak dapat dihapus.');

        if ($user->hasBusinessHistory()) {
            return back()->withErrors([
                'user' => 'Akun memiliki riwayat kendaraan, layanan, transaksi, atau audit sehingga tidak dapat dihapus. Nonaktifkan akun pelanggan untuk mencabut akses tanpa menghapus riwayat.',
            ]);
        }

        $user->delete();
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}