<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // Validasi data dari request
        $validated = $request->validated();

        // Buat user baru
        $username = $validated['username'] ?? Str::slug(Str::before($validated['email'], '@'));
        $username = $username ?: Str::random(8);
        $usernameBase = $username;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $username = $usernameBase . $suffix++;
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $username,
            'no_hp' => $validated['no_hp'] ?? null,
            'password' => Hash::make($validated['password']),
            // PENTING: Role selalu customer untuk registrasi publik
            'role' => 'customer',
        ]);

        return redirect()->route('login')
            ->with('status', 'Akun berhasil dibuat. Silakan masuk dengan akun baru Anda.');
    }
}