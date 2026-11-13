<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_mark_all_own_notifications_as_read_without_changing_other_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $adminNotification = $this->createNotification($admin, 'Notifikasi admin');
        $customerNotification = $this->createNotification($customer, 'Notifikasi pelanggan');
        $otherAdminNotification = $this->createNotification($otherAdmin, 'Notifikasi admin lain');

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Tandai semua dibaca')
            ->assertSee($adminNotification->title)
            ->assertDontSee($customerNotification->title)
            ->assertDontSee($otherAdminNotification->title);

        $this->post(route('admin.notifications.markAsRead', $customerNotification))
            ->assertNotFound();

        $this->actingAs($admin)
            ->from(route('admin.notifications.index'))
            ->post(route('admin.notifications.markAllAsRead'))
            ->assertRedirect(route('admin.notifications.index'))
            ->assertSessionHas('success', 'Semua notifikasi telah ditandai sudah dibaca.');

        $this->assertDatabaseHas('notifications', [
            'id' => $adminNotification->id,
            'is_read' => true,
        ]);
        $this->assertDatabaseHas('notifications', [
            'id' => $customerNotification->id,
            'is_read' => false,
        ]);
        $this->assertDatabaseHas('notifications', [
            'id' => $otherAdminNotification->id,
            'is_read' => false,
        ]);
    }

    private function createNotification(User $user, string $title): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => 'Pesan pengujian.',
            'type' => 'payment',
            'is_read' => false,
        ]);
    }
}
