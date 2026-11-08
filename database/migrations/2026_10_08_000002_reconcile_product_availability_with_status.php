<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_availability_backfill', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->primary();
            $table->boolean('was_active');
        });

        DB::table('products')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('status', 'active')->where('is_active', false);
                })->orWhere(function ($query) {
                    $query->where('status', 'inactive')->where('is_active', true);
                });
            })
            ->orderBy('id')
            ->chunkById(500, function ($products) {
                DB::table('product_availability_backfill')->insert(
                    $products->map(fn ($product) => [
                        'product_id' => $product->id,
                        'was_active' => $product->is_active,
                    ])->all(),
                );

                foreach ($products as $product) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'is_active' => $product->status === 'active',
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('product_availability_backfill')
            ->orderBy('product_id')
            ->chunkById(500, function ($products) {
                foreach ($products as $product) {
                    DB::table('products')
                        ->where('id', $product->product_id)
                        ->update(['is_active' => $product->was_active]);
                }
            }, 'product_id');

        Schema::dropIfExists('product_availability_backfill');
    }
};
