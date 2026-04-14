<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'users']);
    }

    public function test_index()
    {
        Profile::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user, 'sanctum')
            ->get('/api/profiles');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id', 'user_id', 'firstName', 'middleName', 'lastName', 'secondLastName', 'photo_users', 'date_of_birth', 'maritalStatus', 'sex',
                    ],
                ],
            ]);
    }

    public function test_store()
    {
        $data = [
            'user_id' => $this->user->id,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'date_of_birth' => '1985-05-15',
            'maritalStatus' => 'single',
            'sex' => 'M',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->post('/api/profiles', $data);
        $response->assertStatus(201)
            ->assertJson(['message' => 'Perfil creado exitosamente.']);
    }

    public function test_store_succeeds_without_phone()
    {
        $data = [
            'user_id' => $this->user->id,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'date_of_birth' => '1985-05-15',
            'maritalStatus' => 'single',
            'sex' => 'M',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->post('/api/profiles', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->user->id,
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);
    }

    public function test_show()
    {
        $profile = Profile::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user, 'sanctum')
            ->get("/api/profiles/{$profile->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $profile->id);
    }

    public function test_update()
    {
        $profile = Profile::factory()->create(['user_id' => $this->user->id]);
        $data = [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'date_of_birth' => '1985-05-15',
            'maritalStatus' => 'single',
            'sex' => 'M',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->post("/api/profiles/{$profile->id}", $data);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Perfil actualizado exitosamente.']);
    }

    public function test_destroy()
    {
        $profile = Profile::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user, 'sanctum')
            ->delete("/api/profiles/{$profile->id}");
        $response->assertStatus(200)
            ->assertJson(['message' => 'Perfil eliminado exitosamente.']);
    }

    /**
     * add-commerce acepta schedule como string y crea el comercio (onboarding paso 4).
     */
    public function test_add_commerce_to_profile_accepts_schedule_as_string()
    {
        $profile = Profile::factory()->create(['user_id' => $this->user->id]);
        $payload = [
            'profile_id' => $profile->id,
            'business_name' => 'Mi Restaurante',
            'business_type' => 'Restaurante',
            'tax_id' => 'J-12345678-9',
            'address' => 'Av. Principal 123',
            'open' => true,
            'schedule' => 'Lunes a Viernes 8:00-18:00',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/profiles/add-commerce', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $profile->fresh()->commerce->id,
                    'business_name' => 'Mi Restaurante',
                    'address' => 'Av. Principal 123',
                    'open' => true,
                ],
            ]);
        $this->assertDatabaseHas('commerces', [
            'profile_id' => $profile->id,
            'business_name' => 'Mi Restaurante',
        ]);
    }

    /**
     * add-commerce rechaza schedule cuando no es string (ej. objeto/array) y devuelve 400.
     */
    public function test_add_commerce_to_profile_returns_400_when_schedule_is_not_string()
    {
        $profile = Profile::factory()->create(['user_id' => $this->user->id]);
        $payload = [
            'profile_id' => $profile->id,
            'business_name' => 'Otro Restaurante',
            'business_type' => 'Restaurante',
            'tax_id' => 'J-87654321-1',
            'address' => 'Calle Secundaria 456',
            'open' => false,
            'schedule' => ['Lunes' => ['open' => '09:00', 'close' => '18:00']],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/profiles/add-commerce', $payload);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Datos no válidos.',
            ])
            ->assertJsonValidationErrors(['schedule']);
        $this->assertDatabaseMissing('commerces', [
            'profile_id' => $profile->id,
            'business_name' => 'Otro Restaurante',
        ]);
    }

    public function test_add_commerce_to_profile_returns_403_for_foreign_profile()
    {
        $otherUser = User::factory()->create(['role' => 'users']);
        $foreignProfile = Profile::factory()->create(['user_id' => $otherUser->id]);

        $payload = [
            'profile_id' => $foreignProfile->id,
            'business_name' => 'Comercio Ajeno',
            'business_type' => 'Restaurante',
            'tax_id' => 'J-11111111-1',
            'address' => 'Dirección X',
            'open' => false,
            'schedule' => 'Lunes a Viernes 8:00-18:00',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/profiles/add-commerce', $payload);

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);
    }

    public function test_profile_show_update_and_destroy_require_ownership()
    {
        $otherUser = User::factory()->create(['role' => 'users']);
        $foreignProfile = Profile::factory()->create(['user_id' => $otherUser->id]);

        $showResponse = $this->actingAs($this->user, 'sanctum')
            ->get("/api/profiles/{$foreignProfile->id}");
        $showResponse->assertStatus(403);

        $updateResponse = $this->actingAs($this->user, 'sanctum')
            ->post("/api/profiles/{$foreignProfile->id}", [
                'firstName' => 'X',
                'lastName' => 'Y',
                'date_of_birth' => '1990-01-01',
                'maritalStatus' => 'single',
                'sex' => 'M',
            ]);
        $updateResponse->assertStatus(403);

        $destroyResponse = $this->actingAs($this->user, 'sanctum')
            ->delete("/api/profiles/{$foreignProfile->id}");
        $destroyResponse->assertStatus(403);
    }

    public function test_create_commerce_returns_403_for_foreign_user_id(): void
    {
        $otherUser = User::factory()->create(['role' => 'users']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->post('/api/profiles/commerce', [
                'user_id' => $otherUser->id,
                'firstName' => 'Owner',
                'lastName' => 'Foreign',
                'date_of_birth' => '1990-01-01',
                'maritalStatus' => 'single',
                'sex' => 'M',
                'photo_users' => UploadedFile::fake()->image('owner.jpg'),
                'phone' => '04121234567',
                'business_name' => 'Comercio Test',
                'business_type' => 'Restaurante',
                'tax_id' => 'J-12345678-9',
                'address' => 'Av. Principal',
            ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);
    }

    public function test_create_delivery_agent_returns_403_for_foreign_user_id(): void
    {
        $otherUser = User::factory()->create(['role' => 'users']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->post('/api/profiles/delivery-agent', [
                'user_id' => $otherUser->id,
                'firstName' => 'Delivery',
                'lastName' => 'Foreign',
                'date_of_birth' => '1992-01-01',
                'maritalStatus' => 'single',
                'sex' => 'M',
                'photo_users' => UploadedFile::fake()->image('delivery.jpg'),
                'phone' => '04121234567',
                'vehicle_type' => 'Moto',
                'license_number' => 'LIC-123',
            ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);
    }

    public function test_create_delivery_company_returns_403_for_foreign_user_id(): void
    {
        $otherUser = User::factory()->create(['role' => 'users']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->post('/api/profiles/delivery-company', [
                'user_id' => $otherUser->id,
                'firstName' => 'Company',
                'lastName' => 'Foreign',
                'date_of_birth' => '1993-01-01',
                'maritalStatus' => 'single',
                'sex' => 'M',
                'photo_users' => UploadedFile::fake()->image('company.jpg'),
                'phone' => '04121234567',
                'company_name' => 'Company Test',
                'address' => 'Av. Company',
                'ci' => 'V12345678',
            ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'No autorizado']);
    }
}
