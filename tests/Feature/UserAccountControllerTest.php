<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserAccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_returns_profile_data(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/profile/export')
            ->assertOk()
            ->assertJsonStructure(['user', 'profile', 'addresses', 'phones', 'documents', 'exported_at']);
    }

    public function test_privacy_settings_round_trip(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->putJson('/api/user/privacy-settings', [
            'profile_visibility' => false,
            'push_notifications' => false,
        ])->assertOk();

        $this->getJson('/api/user/privacy-settings')
            ->assertOk()
            ->assertJsonPath('data.profile_visibility', false)
            ->assertJsonPath('data.push_notifications', false);
    }

    public function test_delete_account_removes_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->deleteJson('/api/user/account')->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
