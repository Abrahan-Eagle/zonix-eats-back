<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function testIndex()
    {
        $response = $this->actingAs($this->user, 'sanctum')
                        ->get('/api/profiles');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'id', 'user_id', 'firstName', 'middleName', 'lastName', 'secondLastName', 'photo_users', 'date_of_birth', 'maritalStatus', 'sex'
                     ]
                 ]);
    }

    public function testStore()
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

    public function testStoreSucceedsWithoutPhone()
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

    public function testShow()
    {
        $profile = Profile::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user, 'sanctum')
                        ->get("/api/profiles/{$profile->id}");
        $response->assertStatus(200)
                 ->assertJson(['id' => $profile->id]);
    }

    public function testUpdate()
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

    public function testDestroy()
    {
        $profile = Profile::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user, 'sanctum')
                        ->delete("/api/profiles/{$profile->id}");
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Perfil eliminado exitosamente']);
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
}
