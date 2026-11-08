<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_product_form_offers_general_business_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Suku Cadang & Otomotif')
            ->assertSee('Perlengkapan Acara')
            ->assertSee('Transportasi & Penyewaan')
            ->assertSee('Perawatan Pribadi')
            ->assertSee('Rumah Tangga')
            ->assertSee('Lainnya');
    }
}
