<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_complete_onboarding()
    {
        $user = User::factory()->create([
            'completed_onboarding' => false,
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/onboarding/{$user->id}", [
            'completed_onboarding' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['completed_onboarding' => 1]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'completed_onboarding' => 1,
        ]);
    }

    /** @test */
    public function authenticated_user_can_complete_onboarding_and_update_role()
    {
        $user = User::factory()->create([
            'completed_onboarding' => false,
            'role' => 'users',
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/onboarding/{$user->id}", [
            'completed_onboarding' => true,
            'role' => 'commerce',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'completed_onboarding' => 1,
                     'role' => 'commerce',
                 ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'completed_onboarding' => 1,
            'role' => 'commerce',
        ]);
    }

    /** @test */
    public function complete_onboarding_returns_403_when_updating_other_user()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Solo puede actualizar su propio registro; id distinto devuelve 403
        $response = $this->putJson('/api/onboarding/99999', [
            'completed_onboarding' => true,
        ]);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'No autorizado']);
    }

    /** @test */
    public function complete_onboarding_requires_completed_onboarding_boolean()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/onboarding/{$user->id}", []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['completed_onboarding']);
    }

    /** @test */
    public function unauthenticated_user_cannot_complete_onboarding()
    {
        $user = User::factory()->create();

        $response = $this->putJson("/api/onboarding/{$user->id}", [
            'completed_onboarding' => true,
        ]);

        $response->assertStatus(401);
    }
}
