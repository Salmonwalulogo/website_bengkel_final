<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $renamedCategories = [
        'Suku Cadang Bengkel' => 'Suku Cadang & Otomotif',
        'Ban Motor' => 'Ban & Perlengkapan Kendaraan',
        'Oli Motor' => 'Oli & Pelumas',
    ];

    private array $generalCategories = [
        'Perlengkapan Acara' => 'Tenda, lampu, dekorasi, dan perlengkapan acara lainnya.',
        'Peralatan & Perlengkapan' => 'Barang dan perlengkapan untuk berbagai kebutuhan.',
        'Transportasi & Penyewaan' => 'Kendaraan, alat angkut, dan barang yang disewakan.',
        'Perawatan Pribadi' => 'Produk untuk kebutuhan perawatan diri.',
        'Rumah Tangga' => 'Produk dan perlengkapan untuk kebutuhan rumah tangga.',
        'Lainnya' => 'Produk yang belum memiliki kategori khusus.',
    ];

    public function up(): void
    {
        foreach ($this->renamedCategories as $oldName => $newName) {
            DB::table('categories')->where('name', $oldName)->update([
                'name' => $newName,
                'updated_at' => now(),
            ]);
        }

        foreach ($this->generalCategories as $name => $description) {
            $exists = DB::table('categories')->where('name', $name)->exists();

            if (! $exists) {
                DB::table('categories')->insert([
                    'name' => $name,
                    'description' => $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->generalCategories as $name => $description) {
            DB::table('categories')
                ->where('name', $name)
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('products')
                        ->whereColumn('products.category_id', 'categories.id');
                })
                ->delete();
        }

        foreach ($this->renamedCategories as $oldName => $newName) {
            DB::table('categories')
                ->where('name', $newName)
                ->update(['name' => $oldName, 'updated_at' => now()]);
        }
    }
};
