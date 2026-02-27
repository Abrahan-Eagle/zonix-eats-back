<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Commerce;
use App\Models\BusinessType;
use App\Models\Profile;

class CommerceSeeder extends Seeder
{
    private const COMMERCE_ZONES = [
        ['name' => 'Restaurante El Socorro Grill', 'address' => 'Av. Principal El Socorro, Valencia', 'type' => 'Restaurant'],
        ['name' => 'Pizzería El Socorro',          'address' => 'El Socorro, Valencia',               'type' => 'Pizzería'],
        ['name' => 'Café El Socorro',               'address' => 'El Socorro, Valencia',               'type' => 'Cafetería'],
        ['name' => 'Panadería El Socorro',          'address' => 'El Socorro, Valencia',               'type' => 'Panadería'],
        ['name' => 'Comedor El Socorro Express',    'address' => 'El Socorro, Valencia',               'type' => 'Comida Rápida'],
        ['name' => 'Pizzería Los Chorritos',        'address' => 'Calle Los Chorritos, Valencia',      'type' => 'Pizzería'],
        ['name' => 'Restaurante Los Chorritos',     'address' => 'Los Chorritos, Valencia',            'type' => 'Restaurant'],
        ['name' => 'Cafetería Los Chorritos',       'address' => 'Los Chorritos, Valencia',            'type' => 'Cafetería'],
        ['name' => 'Sushi Los Chorritos',           'address' => 'Los Chorritos, Valencia',            'type' => 'Sushi Bar'],
        ['name' => 'Panadería Los Chorritos',       'address' => 'Los Chorritos, Valencia',            'type' => 'Panadería'],
        ['name' => 'Café San Diego',                'address' => 'Centro Comercial San Diego, Valencia','type' => 'Cafetería'],
        ['name' => 'Restaurante San Diego Grill',   'address' => 'San Diego, Valencia',                'type' => 'Restaurant'],
        ['name' => 'Pizzería San Diego',            'address' => 'San Diego, Valencia',                'type' => 'Pizzería'],
        ['name' => 'Comedor San Diego Express',     'address' => 'San Diego, Valencia',                'type' => 'Comida Rápida'],
        ['name' => 'Panadería San Diego',           'address' => 'San Diego, Valencia',                'type' => 'Panadería'],
        ['name' => 'Panadería Bella Florida',       'address' => 'Bella Florida, Valencia',            'type' => 'Panadería'],
        ['name' => 'Café Bella Florida',            'address' => 'Bella Florida, Valencia',            'type' => 'Cafetería'],
        ['name' => 'Restaurante Bella Florida',     'address' => 'Bella Florida, Valencia',            'type' => 'Restaurant'],
        ['name' => 'Pizzería Bella Florida',        'address' => 'Bella Florida, Valencia',            'type' => 'Pizzería'],
        ['name' => 'Sushi Bella Florida',           'address' => 'Bella Florida, Valencia',            'type' => 'Sushi Bar'],
        ['name' => 'Sushi La Honda',                'address' => 'Av. La Honda, Valencia',             'type' => 'Sushi Bar'],
        ['name' => 'Restaurante La Honda',          'address' => 'La Honda, Valencia',                 'type' => 'Restaurant'],
        ['name' => 'Café La Honda',                 'address' => 'La Honda, Valencia',                 'type' => 'Cafetería'],
        ['name' => 'Panadería La Honda',            'address' => 'La Honda, Valencia',                 'type' => 'Panadería'],
        ['name' => 'Comedor La Honda Express',      'address' => 'La Honda, Valencia',                 'type' => 'Comida Rápida'],
        ['name' => 'Comedor Tocuyito Express',      'address' => 'Tocuyito, Carabobo',                'type' => 'Comida Rápida'],
        ['name' => 'Restaurante Tocuyito',          'address' => 'Tocuyito, Carabobo',                'type' => 'Restaurant'],
        ['name' => 'Pizzería Tocuyito',             'address' => 'Tocuyito, Carabobo',                'type' => 'Pizzería'],
        ['name' => 'Cafetería Tocuyito',            'address' => 'Tocuyito, Carabobo',                'type' => 'Cafetería'],
        ['name' => 'Panadería Tocuyito',            'address' => 'Tocuyito, Carabobo',                'type' => 'Panadería'],
        ['name' => 'Restaurante Maracay Centro',    'address' => 'Centro de Maracay, Aragua',         'type' => 'Restaurant'],
        ['name' => 'Café Maracay',                  'address' => 'Maracay, Aragua',                   'type' => 'Cafetería'],
        ['name' => 'Pizzería Maracay',              'address' => 'Maracay, Aragua',                   'type' => 'Pizzería'],
        ['name' => 'Comedor Maracay Express',       'address' => 'Maracay, Aragua',                   'type' => 'Comida Rápida'],
        ['name' => 'Panadería Maracay',             'address' => 'Maracay, Aragua',                   'type' => 'Panadería'],
        ['name' => 'Cafetería Guacara',             'address' => 'Guacara, Carabobo',                 'type' => 'Cafetería'],
        ['name' => 'Restaurante Guacara',           'address' => 'Guacara, Carabobo',                 'type' => 'Restaurant'],
        ['name' => 'Pizzería Guacara',              'address' => 'Guacara, Carabobo',                 'type' => 'Pizzería'],
        ['name' => 'Comedor Guacara Express',       'address' => 'Guacara, Carabobo',                 'type' => 'Comida Rápida'],
        ['name' => 'Panadería Guacara',             'address' => 'Guacara, Carabobo',                 'type' => 'Panadería'],
    ];

    public function run(): void
    {
        $typeMap = BusinessType::pluck('id', 'name')->toArray();
        $ownerProfiles = Profile::take(8)->get();
        $zones = self::COMMERCE_ZONES;
        $count = 0;

        foreach ($ownerProfiles as $zoneIndex => $profile) {
            $profile->user?->update(['role' => 'commerce']);
            for ($j = 0; $j < 5; $j++) {
                $idx = $zoneIndex * 5 + $j;
                $zone = $zones[$idx] ?? $zones[0];
                $typeId = $typeMap[$zone['type']] ?? null;
                Commerce::factory()->create([
                    'profile_id' => $profile->id,
                    'business_name' => $zone['name'],
                    'business_type' => $zone['type'],
                    'business_type_id' => $typeId,
                    'address' => $zone['address'],
                    'open' => true,
                ]);
                $count++;
            }
        }

        $this->command->info("CommerceSeeder: {$count} comercios creados con business_type_id.");
    }
}
