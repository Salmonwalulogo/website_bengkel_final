<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveDemoProductsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_archives_only_the_known_demo_products(): void
    {
        $category = Category::create(['name' => 'Suku Cadang Bengkel']);
        $demoProduct = Product::where('product_code', 'BRG001')->firstOrFail();
        $demoProduct->update([
            'status' => 'active',
            'is_active' => true,
            'stok' => 100,
        ]);
        $realProduct = Product::create([
            'category_id' => $category->id,
            'product_code' => 'REAL001',
            'name' => 'Oli Mesin 10W-40 1 Liter',
            'harga' => 70000,
            'stok' => 12,
            'status' => 'active',
            'is_active' => true,
        ]);

        $migration = require database_path('migrations/2026_10_27_000001_archive_current_products.php');
        $migration->up();

        $this->assertDatabaseHas('products', [
            'id' => $demoProduct->id,
            'status' => 'inactive',
            'is_active' => false,
            'stok' => 0,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $realProduct->id,
            'status' => 'active',
            'is_active' => true,
            'stok' => 12,
        ]);
    }
}
