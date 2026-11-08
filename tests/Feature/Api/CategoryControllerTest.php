<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test')->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/categories', [
            'name' => 'Ban',
            'description' => 'Suku cadang rem',
        ]);
        $createResponse->assertCreated()->assertJsonPath('data.name', 'Ban');

        $categoryId = $createResponse->json('data.id');
        $this->withToken($token)->putJson("/api/categories/{$categoryId}", [
            'name' => 'Sistem Rem',
            'description' => 'Komponen rem kendaraan',
        ])->assertOk()->assertJsonPath('data.name', 'Sistem Rem');

        $this->withToken($token)->deleteJson("/api/categories/{$categoryId}")->assertOk();
        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
    }

    public function test_non_admin_cannot_manage_categories(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/categories', ['name' => 'Ban'])->assertForbidden();
        $this->withToken($token)->putJson('/api/categories/1', ['name' => 'Ban'])->assertForbidden();
        $this->withToken($token)->deleteJson('/api/categories/1')->assertForbidden();
    }
}
