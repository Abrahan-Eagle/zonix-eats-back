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
                 ->assertJson(['completed_onboarding' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'completed_onboarding' => true,
        ]);
    }

    /** @test */
    public function complete_onboarding_returns_404_for_nonexistent_user()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/onboarding/99999', [
            'completed_onboarding' => true,
        ]);

        $response->assertStatus(404)
                 ->assertJson(['error' => 'Usuario no encontrado']);
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
