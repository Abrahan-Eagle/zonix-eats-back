<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'google_id' => 'google_123456',
            'role' => 'user',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'data' => ['user', 'token']]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'user',
        ]);
    }

    /** @test */
    public function user_cannot_register_with_invalid_email(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'role' => 'user',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function user_can_login_with_google(): void
    {
        User::factory()->create([
            'google_id' => 'google_123456',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        $this->postJson('/api/auth/google', [
            'google_id' => 'google_123456',
            'email' => 'test@example.com',
            'name' => 'Test User',
        ])->assertStatus(200)->assertJsonStructure(['success', 'data' => ['user', 'token']]);
    }

    /** @test */
    public function user_can_logout(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/auth/logout')->assertStatus(200)->assertJson(['success' => true]);
    }

    /** @test */
    public function user_can_get_profile(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/auth/user')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['id', 'name', 'email', 'role']]);
    }
}
