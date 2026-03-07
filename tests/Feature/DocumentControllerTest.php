<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200_and_list_for_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        Document::create([
            'profile_id' => $profile->id,
            'type' => 'ci',
            'number_ci' => 12345678,
            'status' => true,
            'approved' => false,
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/documents');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_index_returns_401_when_not_authenticated(): void
    {
        $response = $this->getJson('/api/documents');
        $response->assertStatus(401);
    }

    public function test_store_creates_ci_document(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/documents', [
            'profile_id' => $user->id,
            'type' => 'ci',
            'number_ci' => 12345678,
            'issued_at' => '2020-01-01',
            'expires_at' => '2030-01-01',
        ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Document created successfully']);
        $this->assertDatabaseHas('documents', [
            'profile_id' => $profile->id,
            'type' => 'ci',
            'number_ci' => 12345678,
            'status' => true,
        ]);
    }

    public function test_store_rejects_invalid_type(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/documents', [
            'profile_id' => $user->id,
            'type' => 'passport',
            'number_ci' => 12345678,
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['error' => 'Invalid document type. Only CI and RIF are allowed.']);
    }

    public function test_store_rejects_duplicate_type_per_profile(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        Document::create([
            'profile_id' => $profile->id,
            'type' => 'ci',
            'number_ci' => 11111111,
            'status' => true,
            'approved' => false,
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/documents', [
            'profile_id' => $user->id,
            'type' => 'ci',
            'number_ci' => 12345678,
        ]);

        $response->assertStatus(400);
        $body = $response->json();
        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('already exists', $body['error']);
    }

    public function test_show_returns_documents_for_user(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        Document::create([
            'profile_id' => $profile->id,
            'type' => 'ci',
            'number_ci' => 12345678,
            'status' => true,
            'approved' => false,
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/documents/' . $user->id);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
    }
}
