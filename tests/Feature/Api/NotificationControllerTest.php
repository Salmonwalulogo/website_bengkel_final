<?php

namespace Tests\Feature\Api;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_and_mark_only_their_notifications_as_read(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $notification = Notification::create([
            'user_id' => $customer->id,
            'title' => 'Booking diterima',
            'message' => 'Booking servis Anda telah dikonfirmasi.',
            'type' => 'booking',
        ]);
        $otherNotification = Notification::create([
            'user_id' => $otherCustomer->id,
            'title' => 'Privat',
            'message' => 'Notifikasi milik pelanggan lain.',
            'type' => 'system',
        ]);
        $token = $customer->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $notification->id);

        $this->withToken($token)
            ->postJson("/api/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('data.is_read', true);
        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'is_read' => true]);

        $this->withToken($token)
            ->postJson("/api/notifications/{$otherNotification->id}/read")
            ->assertNotFound();
    }

    public function test_non_customers_cannot_access_customer_notifications(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/notifications')->assertForbidden();
    }
}
