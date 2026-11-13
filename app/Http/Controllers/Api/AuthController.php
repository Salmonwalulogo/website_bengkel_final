<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['nullable', 'string', 'email'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $identifier = $request->input('email', $request->input('username'));

        if (blank($identifier)) {
            throw ValidationException::withMessages([
                'login' => ['Email atau username wajib diisi.'],
            ]);
        }

        $credentials = [
            'password' => $request->password,
            'is_active' => true,
        ];

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $identifier;
        } else {
            $credentials['username'] = $identifier;
        }

        if (! Auth::guard('web')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'login' => ['Kredensial tidak valid.'],
            ]);
        }

        $user = Auth::guard('web')->user();
        $token = $user->createToken('android-app')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'username', 'role', 'no_hp', 'photo']),
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $username = $validated['username'] ?? strstr($validated['email'], '@', true);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $username,
            'no_hp' => $validated['no_hp'] ?? null,
            'role' => 'customer', // Always customer
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        $token = $user->createToken('android-app')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'username', 'role', 'no_hp', 'photo']),
        ], 201);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->only(['id', 'name', 'email', 'username', 'role', 'no_hp', 'photo']),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }
}
