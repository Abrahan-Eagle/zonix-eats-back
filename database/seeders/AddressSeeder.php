<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Address;
use App\Models\Profile;
use App\Models\Commerce;
use App\Models\City;

class AddressSeeder extends Seeder
{
    /**
     * GPS de zonas Carabobo/Valencia Venezuela para nearby-places.
     * Coordenadas reales: El Socorro, Los Chorritos, San Diego, Bella Florida,
     * La Honda, Tocuyito, Maracay, Guacara.
     */
    private const ZONE_COORDINATES = [
        ['street' => 'Av. Principal El Socorro', 'lat' => 10.1820, 'lng' => -68.0080],   // El Socorro
        ['street' => 'Calle Los Chorritos', 'lat' => 10.1750, 'lng' => -67.9980],       // Los Chorritos
        ['street' => 'Centro Comercial San Diego', 'lat' => 10.2558, 'lng' => -67.9536], // San Diego
        ['street' => 'Bella Florida', 'lat' => 10.1920, 'lng' => -68.0120],             // Bella Florida
        ['street' => 'Av. La Honda', 'lat' => 10.2050, 'lng' => -68.0020],              // La Honda
        ['street' => 'Tocuyito Centro', 'lat' => 10.1136, 'lng' => -68.0878],           // Tocuyito
        ['street' => 'Centro Maracay', 'lat' => 10.2466, 'lng' => -67.5958],            // Maracay
        ['street' => 'Guacara Centro', 'lat' => 10.2288, 'lng' => -67.8742],            // Guacara
    ];

    public function run(): void
    {
        $profiles = Profile::all();
        if ($profiles->isEmpty()) {
            $this->command->warn('No hay perfiles para crear direcciones.');
            return;
        }

        $cityValencia = City::where('name', 'Valencia')->first() ?? City::find(90);
        $cityId = $cityValencia?->id ?? 90;

        $commerces = Commerce::orderBy('id')->get();
        $ownerProfileIds = $commerces->pluck('profile_id')->unique()->values()->toArray();
        $profileToZone = array_flip($ownerProfileIds);
        $zones = self::ZONE_COORDINATES;

        foreach ($profiles as $profile) {
            $isCommerce = isset($profileToZone[$profile->id]);
            $zoneIndex = $profileToZone[$profile->id] ?? null;
            $zone = ($zoneIndex !== null && isset($zones[$zoneIndex])) ? $zones[$zoneIndex] : null;

            if ($zone) {
                Address::factory()->default()->create([
                    'profile_id' => $profile->id,
                    'city_id' => $cityId,
                    'street' => $zone['street'],
                    'house_number' => fake()->buildingNumber(),
                    'latitude' => $zone['lat'] + (fake()->randomFloat(4, -0.003, 0.003)),
                    'longitude' => $zone['lng'] + (fake()->randomFloat(4, -0.003, 0.003)),
                    'is_default' => true,
                ]);
            } else {
                Address::factory()->default()->create([
                    'profile_id' => $profile->id,
                    'is_default' => true,
                    'latitude' => fake()->latitude(10.08, 10.28),
                    'longitude' => fake()->longitude(-68.12, -67.55),
                ]);
            }

            if ($isCommerce && $zone && rand(0, 1)) {
                Address::factory()->create([
                    'profile_id' => $profile->id,
                    'city_id' => $cityId,
                    'street' => $zone['street'] . ' - Local',
                    'latitude' => $zone['lat'],
                    'longitude' => $zone['lng'],
                    'is_default' => false,
                ]);
            } elseif (!$isCommerce && rand(0, 1)) {
                Address::factory()->create([
                    'profile_id' => $profile->id,
                    'is_default' => false,
                ]);
            }
        }

        $this->command->info('AddressSeeder ejecutado: direcciones Carabobo/Valencia (1 dueño = múltiples comercios).');
    }
}
