<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $demoProducts = [
            'BRG001' => 'Oli Mesin 10W-40 1 Liter',
            'BRG002' => 'Busi Motor NGK',
            'BRG003' => 'Kampas Rem Depan Motor',
            'BRG004' => 'Filter Oli Motor',
            'BRG005' => 'Aki Motor 12V',
            'BRG006' => 'Rantai Motor DID',
        ];

        foreach ($demoProducts as $productCode => $name) {
            DB::table('products')
                ->where('product_code', $productCode)
                ->where('name', $name)
                ->update([
                    'status' => 'inactive',
                    'is_active' => false,
                    'stok' => 0,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
    }
};
