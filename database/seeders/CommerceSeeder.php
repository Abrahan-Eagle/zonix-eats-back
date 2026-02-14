<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Commerce;
use App\Models\Profile;

class CommerceSeeder extends Seeder
{
    /** 8 zonas, 5 comercios por zona = 40 comercios */
    private const COMMERCE_ZONES = [
        ['name' => 'Restaurante El Socorro Grill', 'address' => 'Av. Principal El Socorro, Valencia'],
        ['name' => 'Pizzería El Socorro', 'address' => 'El Socorro, Valencia'],
        ['name' => 'Café El Socorro', 'address' => 'El Socorro, Valencia'],
        ['name' => 'Panadería El Socorro', 'address' => 'El Socorro, Valencia'],
        ['name' => 'Comedor El Socorro Express', 'address' => 'El Socorro, Valencia'],
        ['name' => 'Pizzería Los Chorritos', 'address' => 'Calle Los Chorritos, Valencia'],
        ['name' => 'Restaurante Los Chorritos', 'address' => 'Los Chorritos, Valencia'],
        ['name' => 'Cafetería Los Chorritos', 'address' => 'Los Chorritos, Valencia'],
        ['name' => 'Sushi Los Chorritos', 'address' => 'Los Chorritos, Valencia'],
        ['name' => 'Panadería Los Chorritos', 'address' => 'Los Chorritos, Valencia'],
        ['name' => 'Café San Diego', 'address' => 'Centro Comercial San Diego, Valencia'],
        ['name' => 'Restaurante San Diego Grill', 'address' => 'San Diego, Valencia'],
        ['name' => 'Pizzería San Diego', 'address' => 'San Diego, Valencia'],
        ['name' => 'Comedor San Diego Express', 'address' => 'San Diego, Valencia'],
        ['name' => 'Panadería San Diego', 'address' => 'San Diego, Valencia'],
        ['name' => 'Panadería Bella Florida', 'address' => 'Bella Florida, Valencia'],
        ['name' => 'Café Bella Florida', 'address' => 'Bella Florida, Valencia'],
        ['name' => 'Restaurante Bella Florida', 'address' => 'Bella Florida, Valencia'],
        ['name' => 'Pizzería Bella Florida', 'address' => 'Bella Florida, Valencia'],
        ['name' => 'Sushi Bella Florida', 'address' => 'Bella Florida, Valencia'],
        ['name' => 'Sushi La Honda', 'address' => 'Av. La Honda, Valencia'],
        ['name' => 'Restaurante La Honda', 'address' => 'La Honda, Valencia'],
        ['name' => 'Café La Honda', 'address' => 'La Honda, Valencia'],
        ['name' => 'Panadería La Honda', 'address' => 'La Honda, Valencia'],
        ['name' => 'Comedor La Honda Express', 'address' => 'La Honda, Valencia'],
        ['name' => 'Comedor Tocuyito Express', 'address' => 'Tocuyito, Carabobo'],
        ['name' => 'Restaurante Tocuyito', 'address' => 'Tocuyito, Carabobo'],
        ['name' => 'Pizzería Tocuyito', 'address' => 'Tocuyito, Carabobo'],
        ['name' => 'Cafetería Tocuyito', 'address' => 'Tocuyito, Carabobo'],
        ['name' => 'Panadería Tocuyito', 'address' => 'Tocuyito, Carabobo'],
        ['name' => 'Restaurante Maracay Centro', 'address' => 'Centro de Maracay, Aragua'],
        ['name' => 'Café Maracay', 'address' => 'Maracay, Aragua'],
        ['name' => 'Pizzería Maracay', 'address' => 'Maracay, Aragua'],
        ['name' => 'Comedor Maracay Express', 'address' => 'Maracay, Aragua'],
        ['name' => 'Panadería Maracay', 'address' => 'Maracay, Aragua'],
        ['name' => 'Cafetería Guacara', 'address' => 'Guacara, Carabobo'],
        ['name' => 'Restaurante Guacara', 'address' => 'Guacara, Carabobo'],
        ['name' => 'Pizzería Guacara', 'address' => 'Guacara, Carabobo'],
        ['name' => 'Comedor Guacara Express', 'address' => 'Guacara, Carabobo'],
        ['name' => 'Panadería Guacara', 'address' => 'Guacara, Carabobo'],
    ];

    /**
     * Run the database seeds.
     * 1 dueño puede tener 1 o más comercios. 8 dueños, cada uno con 5 comercios en su zona = 40 comercios.
     */
    public function run(): void
    {
        $ownerProfiles = Profile::take(8)->get();
        $zones = self::COMMERCE_ZONES;
        $count = 0;

        foreach ($ownerProfiles as $zoneIndex => $profile) {
            $profile->user?->update(['role' => 'commerce']);
            // 5 comercios por zona, asignados al mismo dueño
            for ($j = 0; $j < 5; $j++) {
                $idx = $zoneIndex * 5 + $j;
                $zone = $zones[$idx] ?? $zones[0];
                Commerce::factory()->create([
                    'profile_id' => $profile->id,
                    'business_name' => $zone['name'],
                    'address' => $zone['address'],
                    'open' => true,
                ]);
                $count++;
            }
        }

        $this->command->info("CommerceSeeder: {$count} comercios creados (8 dueños x 5 comercios c/u).");
    }
}
