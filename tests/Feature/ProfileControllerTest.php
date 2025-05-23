// tests/Feature/ProfileControllerTest.php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $response = $this->get('/api/profiles');
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
            'user_id' => 1,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'date_of_birth' => '1985-05-15',
            'maritalStatus' => 'single',
            'sex' => 'M'
        ];

        $response = $this->post('/api/profiles', $data);
        $response->assertStatus(201)
                 ->assertJson(['message' => 'Perfil creado exitosamente.']);
    }

    public function testShow()
    {
        $profile = Profile::factory()->create();
        $response = $this->get("/api/profiles/{$profile->id}");
        $response->assertStatus(200)
                 ->assertJson(['id' => $profile->id]);
    }

    public function testUpdate()
    {
        $profile = Profile::factory()->create();
        $data = ['firstName' => 'Jane'];

        $response = $this->post("/api/profiles/{$profile->id}", $data);
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Perfil actualizado exitosamente.']);
    }

    public function testDestroy()
    {
        $profile = Profile::factory()->create();
        $response = $this->delete("/api/profiles/{$profile->id}");
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Perfil eliminado exitosamente']);
    }
}
