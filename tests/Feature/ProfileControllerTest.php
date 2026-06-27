<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'user']);
    }

    public function test_index(): void
    {
        Profile::factory()->create(['user_id' => $this->user->id]);
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/profiles')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_store(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/profiles', [
                'user_id' => $this->user->id,
                'firstName' => 'John',
                'lastName' => 'Doe',
                'date_of_birth' => '1985-05-15',
                'maritalStatus' => 'single',
                'sex' => 'M',
            ])
            ->assertStatus(201);
    }

    public function test_show(): void
    {
        $profile = Profile::factory()->create(['user_id' => $this->user->id]);
        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/profiles/{$profile->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $profile->id);
    }

    public function test_profile_requires_ownership(): void
    {
        $other = User::factory()->create(['role' => 'user']);
        $profile = Profile::factory()->create(['user_id' => $other->id]);
        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/profiles/{$profile->id}")
            ->assertStatus(403);
    }
}
