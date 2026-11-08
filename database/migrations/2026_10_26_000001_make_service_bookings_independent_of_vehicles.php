<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('services', 'category')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('category', 100)->default('Lainnya');
            });
        }

        if (!Schema::hasColumn('bookings', 'request_details')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->text('request_details')->nullable();
            });
        }

        if (!Schema::hasColumn('bookings', 'quoted_price')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->decimal('quoted_price', 12, 2)->nullable();
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bookings MODIFY vehicle_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreignId('vehicle_id')->nullable()->change();
            });
        }

        if (!Schema::hasColumn('transactions', 'booking_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('booking_id')->nullable()->unique()->constrained('bookings')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'booking_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['booking_id']);
                $table->dropUnique(['booking_id']);
                $table->dropColumn('booking_id');
            });
        }

        if (Schema::hasColumn('bookings', 'request_details')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('request_details');
            });
        }

        if (Schema::hasColumn('services', 'category')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};
