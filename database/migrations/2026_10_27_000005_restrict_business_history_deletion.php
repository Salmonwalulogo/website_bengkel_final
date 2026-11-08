<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $foreignKeys = [
        ['products', 'category_id', 'categories', 'restrict'],
        ['transactions', 'user_id', 'users', 'restrict'],
        ['transactions', 'customer_id', 'users', 'restrict'],
        ['transactions', 'booking_id', 'bookings', 'restrict'],
        ['bookings', 'user_id', 'users', 'restrict'],
        ['bookings', 'service_id', 'services', 'restrict'],
        ['vehicles', 'user_id', 'users', 'restrict'],
        ['transaction_details', 'transaction_id', 'transactions', 'restrict'],
        ['payments', 'transaction_id', 'transactions', 'restrict'],
        ['service_details', 'booking_id', 'bookings', 'restrict'],
    ];

    public function up(): void
    {
        foreach ($this->foreignKeys as [$table, $column, $referencedTable]) {
            $this->replaceForeignKey($table, $column, $referencedTable, 'restrict');
        }
    }

    public function down(): void
    {
        foreach ($this->foreignKeys as [$table, $column, $referencedTable]) {
            $onDelete = match ($table . '.' . $column) {
                'transactions.customer_id', 'transactions.booking_id' => 'null',
                default => 'cascade',
            };

            $this->replaceForeignKey($table, $column, $referencedTable, $onDelete);
        }
    }

    private function replaceForeignKey(string $table, string $column, string $referencedTable, string $onDelete): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($column, $referencedTable, $onDelete): void {
            $blueprint->dropForeign([$column]);
            $foreignKey = $blueprint->foreign($column)->references('id')->on($referencedTable);

            match ($onDelete) {
                'restrict' => $foreignKey->restrictOnDelete(),
                'null' => $foreignKey->nullOnDelete(),
                default => $foreignKey->cascadeOnDelete(),
            };
        });
    }
};
