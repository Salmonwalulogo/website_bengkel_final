<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('bookings', 'quoted_price')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->decimal('quoted_price', 12, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'quoted_price')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('quoted_price');
            });
        }
    }
};
