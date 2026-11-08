<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('receipt_number')->nullable()->after('invoice_number');
        });

        DB::table('transactions')
            ->select(['id', 'created_at'])
            ->orderBy('id')
            ->chunkById(500, function ($transactions): void {
                foreach ($transactions as $transaction) {
                    $date = $transaction->created_at
                        ? Carbon::parse($transaction->created_at)->format('Ymd')
                        : now()->format('Ymd');

                    DB::table('transactions')
                        ->where('id', $transaction->id)
                        ->update([
                            'receipt_number' => 'STR-' . $date . '-' . str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT),
                        ]);
                }
            });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unique('receipt_number');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['receipt_number']);
            $table->dropColumn('receipt_number');
        });
    }
};
