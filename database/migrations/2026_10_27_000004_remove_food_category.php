<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $category = DB::table('categories')->where('name', 'Makanan & Minuman')->first();

        if (! $category) {
            return;
        }

        if (DB::table('products')->where('category_id', $category->id)->exists()) {
            throw new \RuntimeException('Kategori Makanan & Minuman masih digunakan oleh produk. Pindahkan produk tersebut ke kategori yang sesuai sebelum menghapus kategori.');
        }

        DB::table('categories')->where('id', $category->id)->delete();
    }

    public function down(): void
    {
        if (! DB::table('categories')->where('name', 'Makanan & Minuman')->exists()) {
            DB::table('categories')->insert([
                'name' => 'Makanan & Minuman',
                'description' => 'Produk makanan dan minuman.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
