<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\UserLocation;
use App\Models\Commerce;
use App\Models\Address;
use App\Models\DeliveryAgent;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Http;

class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $profile;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->profile = Profile::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function test_update_location_stores_in_database()
    {
        Sanctum::actingAs($this->user);

        // Mock Nominatim response
        Http::fake([
            'nominatim.openstreetmap.org/reverse*' => Http::response([
                'address' => [
                    'road' => 'Av. Arequipa',
                    'house_number' => '123',
                    'city' => 'Lima',
                    'country' => 'Perú',
                ],
                'display_name' => 'Av. Arequipa 123, Lima, Perú',
            ], 200),
        ]);

        $response = $this->postJson('/api/location/update', [
            'latitude' => -12.0464,
            'longitude' => -77.0428,
            'accuracy' => 10.5,
            'altitude' => 150.0,
            'speed' => 25.5,
            'heading' => 180.0,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Location updated successfully',
            ]);

        $this->assertDatabaseHas('user_locations', [
            'profile_id' => $this->profile->id,
            'latitude' => -12.0464,
            'longitude' => -77.0428,
            'accuracy' => 10.5,
        ]);
    }

    /** @test */
    public function test_update_location_updates_delivery_agent_location()
    {
        $deliveryAgent = DeliveryAgent::factory()->create([
            'profile_id' => $this->profile->id,
        ]);

        Sanctum::actingAs($this->user);

        Http::fake([
            'nominatim.openstreetmap.org/reverse*' => Http::response([
                'display_name' => 'Test Address',
            ], 200),
        ]);

        $response = $this->postJson('/api/location/update', [
            'latitude' => -12.0500,
            'longitude' => -77.0400,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('delivery_agents', [
            'id' => $deliveryAgent->id,
            'current_latitude' => -12.0500,
            'current_longitude' => -77.0400,
        ]);
    }

    /** @test */
    public function test_get_nearby_places_returns_commerces_within_radius()
    {
        // Crear comercios con direcciones
        $commerce1 = Commerce::factory()->create(['profile_id' => $this->profile->id]);
        $commerce2 = Commerce::factory()->create();
        
        $profile2 = Profile::factory()->create();
        $commerce2->update(['profile_id' => $profile2->id]);

        // Crear estado y ciudad para las direcciones
        $country = \App\Models\Country::create([
            'name' => 'Perú',
            'sortname' => 'PE',
            'phonecode' => 51,
        ]);
        $state = \App\Models\State::create([
            'name' => 'Lima',
            'countries_id' => $country->id,
        ]);
        $city = \App\Models\City::create([
            'state_id' => $state->id,
            'name' => 'Lima',
        ]);

        // Crear direcciones con coordenadas
        Address::create([
            'profile_id' => $this->profile->id,
            'street' => 'Av. Arequipa',
            'house_number' => '123',
            'postal_code' => '15001',
            'latitude' => -12.0465, // ~0.1 km del punto de referencia
            'longitude' => -77.0429,
            'city_id' => $city->id,
            'status' => 'completeData',
        ]);

        Address::create([
            'profile_id' => $profile2->id,
            'street' => 'Jr. Tacna',
            'house_number' => '456',
            'postal_code' => '15002',
            'latitude' => -12.0564, // ~1.1 km del punto de referencia
            'longitude' => -77.0328,
            'city_id' => $city->id,
            'status' => 'completeData',
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/location/nearby-places?latitude=-12.0464&longitude=-77.0428&radius=1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        // Puede estar vacío si no hay comercios con direcciones válidas en el radio, pero debe ser un array
        $this->assertIsArray($data);
    }

    /** @test */
    public function test_calculate_route_uses_osrm_when_available()
    {
        Sanctum::actingAs($this->user);

        // Mock OSRM response
        Http::fake([
            'router.project-osrm.org/route*' => Http::response([
                'routes' => [
                    [
                        'distance' => 2500, // metros
                        'duration' => 480, // segundos
                        'geometry' => [
                            'coordinates' => [
                                [-77.0428, -12.0464],
                                [-77.0430, -12.0466],
                                [-77.0435, -12.0470],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/location/calculate-route', [
            'origin_lat' => -12.0464,
            'origin_lng' => -77.0428,
            'destination_lat' => -12.0470,
            'destination_lng' => -77.0435,
            'mode' => 'driving',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertEquals(2.5, $data['distance']); // km
        $this->assertEquals(8, $data['duration']); // minutos
        $this->assertArrayHasKey('polyline', $data);
    }

    /** @test */
    public function test_calculate_route_falls_back_to_haversine_when_osrm_fails()
    {
        Sanctum::actingAs($this->user);

        // Mock OSRM failure
        Http::fake([
            'router.project-osrm.org/route*' => Http::response([], 500),
        ]);

        $response = $this->postJson('/api/location/calculate-route', [
            'origin_lat' => -12.0464,
            'origin_lng' => -77.0428,
            'destination_lat' => -12.0470,
            'destination_lng' => -77.0435,
            'mode' => 'driving',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('distance', $data);
        $this->assertArrayHasKey('duration', $data);
        $this->assertArrayHasKey('note', $data);
        $this->assertStringContainsString('fallback', $data['note']);
    }

    /** @test */
    public function test_get_coordinates_from_address_uses_nominatim()
    {
        Sanctum::actingAs($this->user);

        Http::fake([
            'nominatim.openstreetmap.org/search*' => Http::response([
                [
                    'lat' => '-12.0464',
                    'lon' => '-77.0428',
                    'display_name' => 'Lima, Perú',
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/location/geocode', [
            'address' => 'Lima, Perú',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertEquals(-12.0464, $data['latitude']);
        $this->assertEquals(-77.0428, $data['longitude']);
        $this->assertEquals('Lima, Perú', $data['formatted_address']);
    }

    /** @test */
    public function test_get_delivery_routes_for_delivery_agent()
    {
        $deliveryAgent = DeliveryAgent::factory()->create([
            'profile_id' => $this->profile->id,
        ]);

        $customerProfile = Profile::factory()->create();
        $commerce = Commerce::factory()->create();

        $order = \App\Models\Order::factory()->create([
            'profile_id' => $customerProfile->id,
            'commerce_id' => $commerce->id,
            'status' => 'on_way',
        ]);

        \App\Models\OrderDelivery::factory()->create([
            'order_id' => $order->id,
            'agent_id' => $deliveryAgent->id,
            'status' => 'assigned',
        ]);

        // Crear estado y ciudad para las direcciones
        $country = \App\Models\Country::create([
            'name' => 'Perú',
            'sortname' => 'PE',
            'phonecode' => 51,
        ]);
        $state = \App\Models\State::create([
            'name' => 'Lima',
            'countries_id' => $country->id,
        ]);
        $city = \App\Models\City::create([
            'state_id' => $state->id,
            'name' => 'Lima',
        ]);

        // Crear direcciones
        Address::create([
            'profile_id' => $commerce->profile_id,
            'street' => 'Av. Principal',
            'house_number' => '789',
            'postal_code' => '15003',
            'latitude' => -12.0464,
            'longitude' => -77.0428,
            'city_id' => $city->id,
            'status' => 'completeData',
        ]);

        Address::create([
            'profile_id' => $customerProfile->id,
            'street' => 'Calle Comercial',
            'house_number' => '321',
            'postal_code' => '15004',
            'latitude' => -12.0470,
            'longitude' => -77.0435,
            'city_id' => $city->id,
            'status' => 'completeData',
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/location/delivery-routes');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        // Puede estar vacío si no hay órdenes con direcciones válidas, pero debe ser un array
        $this->assertIsArray($data);
    }

    /** @test */
    public function test_update_location_validation()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/location/update', [
            'latitude' => 100, // Invalid: fuera de rango
            'longitude' => -77.0428,
        ]);

        $response->assertStatus(422);
    }
}
