<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSiteBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_settings_show_current_site_logo_and_grouped_navigation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.settings.general'))
            ->assertOk()
            ->assertSee('Logo Website')
            ->assertSee('submenu-master')
            ->assertSee('submenu-pengaturan');
    }

    public function test_admin_can_upload_and_replace_the_public_site_logo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.settings.general.update'), [
                'nama_bengkel' => 'UD Rumah Daun',
                'telepon' => '+62 851-6502-1473',
                'site_logo' => UploadedFile::fake()->image('logo-awal.png'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $firstLogo = \Illuminate\Support\Facades\DB::table('settings')
            ->where('key', 'site_logo')
            ->value('value');

        $this->assertNotEmpty($firstLogo);
        Storage::disk('public')->assertExists($firstLogo);
        $this->get(route('home'))->assertOk()->assertSee('storage/' . $firstLogo);

        $this->actingAs($admin)
            ->put(route('admin.settings.general.update'), [
                'nama_bengkel' => 'UD Rumah Daun',
                'telepon' => '+62 851-6502-1473',
                'site_logo' => UploadedFile::fake()->image('logo-pengganti.png'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $replacementLogo = \Illuminate\Support\Facades\DB::table('settings')
            ->where('key', 'site_logo')
            ->value('value');

        $this->assertNotSame($firstLogo, $replacementLogo);
        Storage::disk('public')->assertMissing($firstLogo);
        Storage::disk('public')->assertExists($replacementLogo);
    }
}
