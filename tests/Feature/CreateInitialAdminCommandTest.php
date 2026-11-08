<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateInitialAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_first_admin_with_a_hashed_password(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Nama admin', 'Admin Rumah Daun')
            ->expectsQuestion('Email admin', 'admin@example.com')
            ->expectsQuestion('Username admin', 'admin')
            ->expectsQuestion('Password admin (minimal 12 karakter, huruf besar/kecil, angka, dan simbol)', 'StrongAdmin123!')
            ->expectsQuestion('Konfirmasi password admin', 'StrongAdmin123!')
            ->expectsConfirmation('Buat akun admin pertama sekarang?', 'yes')
            ->expectsOutput('Akun admin berhasil dibuat. Simpan kredensial di tempat yang aman.')
            ->assertExitCode(0);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('StrongAdmin123!', $admin->password));
    }

    public function test_it_refuses_to_create_an_admin_when_one_already_exists(): void
    {
        User::factory()->create(['role' => 'admin']);

        $this->artisan('app:create-admin')
            ->expectsOutput('Akun admin sudah ada. Buat akun tambahan dari halaman admin.')
            ->assertExitCode(1);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_it_rejects_a_weak_initial_admin_password(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Nama admin', 'Admin Rumah Daun')
            ->expectsQuestion('Email admin', 'admin@example.com')
            ->expectsQuestion('Username admin', 'admin')
            ->expectsQuestion('Password admin (minimal 12 karakter, huruf besar/kecil, angka, dan simbol)', 'password')
            ->expectsQuestion('Konfirmasi password admin', 'password')
            ->expectsOutputToContain('The password field must be at least 12 characters.')
            ->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }
}
