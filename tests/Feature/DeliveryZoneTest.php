<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\DeliveryZone;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class DeliveryZoneTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_get_delivery_zones_returns_active_zones()
    {
        // Crear zonas de entrega
        $zone1 = DeliveryZone::create([
            'name' => 'Zona Centro',
            'center_latitude' => -12.0464,
            'center_longitude' => -77.0428,
            'radius' => 5.0,
            'delivery_fee' => 3.00,
            'delivery_time' => 30,
            'is_active' => true,
        ]);

        $zone2 = DeliveryZone::create([
            'name' => 'Zona Norte',
            'center_latitude' => -12.0564,
            'center_longitude' => -77.0328,
            'radius' => 7.0,
            'delivery_fee' => 5.00,
            'delivery_time' => 45,
            'is_active' => true,
        ]);

        // Zona inactiva (no debe aparecer)
        DeliveryZone::create([
            'name' => 'Zona Inactiva',
            'center_latitude' => -12.0664,
            'center_longitude' => -77.0228,
            'radius' => 3.0,
            'delivery_fee' => 2.00,
            'delivery_time' => 20,
            'is_active' => false,
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/location/delivery-zones');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(2, $data); // Solo las zonas activas
        
        // Verificar que las zonas activas están presentes
        $zoneNames = collect($data)->pluck('name')->toArray();
        $this->assertContains('Zona Centro', $zoneNames);
        $this->assertContains('Zona Norte', $zoneNames);
        $this->assertNotContains('Zona Inactiva', $zoneNames);
    }

    /** @test */
    public function test_delivery_zone_contains_location()
    {
        $zone = DeliveryZone::create([
            'name' => 'Zona Centro',
            'center_latitude' => -12.0464,
            'center_longitude' => -77.0428,
            'radius' => 5.0, // 5 km
            'delivery_fee' => 3.00,
            'delivery_time' => 30,
            'is_active' => true,
        ]);

        // Ubicación dentro de la zona (aproximadamente 1 km del centro)
        $this->assertTrue($zone->containsLocation(-12.0465, -77.0429));

        // Ubicación fuera de la zona (aproximadamente 10 km del centro)
        $this->assertFalse($zone->containsLocation(-12.1364, -77.1328));
    }

    /** @test */
    public function test_delivery_zone_scope_active()
    {
        DeliveryZone::create([
            'name' => 'Zona Activa',
            'center_latitude' => -12.0464,
            'center_longitude' => -77.0428,
            'radius' => 5.0,
            'is_active' => true,
        ]);

        DeliveryZone::create([
            'name' => 'Zona Inactiva',
            'center_latitude' => -12.0564,
            'center_longitude' => -77.0328,
            'radius' => 7.0,
            'is_active' => false,
        ]);

        $activeZones = DeliveryZone::active()->get();
        
        $this->assertCount(1, $activeZones);
        $this->assertEquals('Zona Activa', $activeZones->first()->name);
    }
}
