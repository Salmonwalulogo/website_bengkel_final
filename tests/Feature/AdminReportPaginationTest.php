<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_uses_bootstrap_pagination_instead_of_oversized_tailwind_icons(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cashier = User::factory()->create(['role' => 'kasir']);

        foreach (range(1, 11) as $index) {
            Transaction::create([
                'user_id' => $cashier->id,
                'invoice_number' => sprintf('TEST-%s-%06d', now()->format('Ymd'), $index),
                'total_payment' => 10000,
                'status' => 'completed',
                'payment_method' => 'cash',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('<ul class="pagination"', false)
            ->assertSee('class="page-link"', false)
            ->assertDontSee('<svg', false);
    }
}
