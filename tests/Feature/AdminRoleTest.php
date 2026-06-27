<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(2)->user()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/users');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_list_users(): void
    {
        $user = User::factory()->user()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/admin/users')->assertStatus(403);
    }

    public function test_admin_can_show_user_with_profile_only(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->user()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/admin/users/{$target->id}");
        $response->assertStatus(200)
            ->assertJsonPath('id', $target->id)
            ->assertJsonStructure(['id', 'email', 'profile']);
    }
}
