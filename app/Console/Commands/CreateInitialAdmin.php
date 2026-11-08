<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateInitialAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Create the first administrator account interactively';

    public function handle(): int
    {
        if (User::where('role', 'admin')->exists()) {
            $this->error('Akun admin sudah ada. Buat akun tambahan dari halaman admin.');

            return self::FAILURE;
        }

        $attributes = [
            'name' => $this->ask('Nama admin'),
            'email' => $this->ask('Email admin'),
            'username' => $this->ask('Username admin'),
            'password' => $this->secret('Password admin (minimal 12 karakter, huruf besar/kecil, angka, dan simbol)'),
            'password_confirmation' => $this->secret('Konfirmasi password admin'),
        ];

        $validator = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if (! $this->confirm('Buat akun admin pertama sekarang?', false)) {
            $this->warn('Pembuatan akun admin dibatalkan.');

            return self::FAILURE;
        }

        $validated = $validator->validated();
        unset($validated['password_confirmation']);
        $validated['role'] = 'admin';
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        $this->info('Akun admin berhasil dibuat. Simpan kredensial di tempat yang aman.');

        return self::SUCCESS;
    }
}
