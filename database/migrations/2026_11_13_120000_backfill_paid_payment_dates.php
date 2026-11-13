<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payments')
            ->where('status', 'paid')
            ->whereNull('payment_date')
            ->update(['payment_date' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
    }
};
