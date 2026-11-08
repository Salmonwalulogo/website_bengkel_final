<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transactions', 'customer_id')) {
            return;
        }

        $customerIds = DB::table('users')->where('role', 'customer')->select('id');
        DB::table('transactions')
            ->whereNull('customer_id')
            ->whereIn('user_id', $customerIds)
            ->update(['customer_id' => DB::raw('user_id')]);
    }

    public function down(): void
    {
    }
};
