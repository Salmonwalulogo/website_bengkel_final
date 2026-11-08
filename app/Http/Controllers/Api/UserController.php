<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $filters = $request->validate([
            'role' => ['sometimes', 'string', Rule::in(['admin', 'kasir', 'customer'])],
        ]);
        $query = User::query();

        if ($user->isKasir()) {
            $query->where('role', 'customer');
        } elseif (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        $users = $query->latest()->get(['id', 'name', 'email', 'username', 'no_hp', 'role', 'created_at']);

        return response()->json(['data' => $users]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:admin,kasir,customer'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'no_hp' => $validated['no_hp'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Pengguna berhasil ditambahkan', 'data' => $newUser], 201);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (array_key_exists('no_hp', $validated)) {
            $user->no_hp = $validated['no_hp'];
        }
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['message' => 'Profil berhasil diperbarui', 'user' => $user->only(['id', 'name', 'email', 'username', 'role', 'no_hp'])]);
    }
}
