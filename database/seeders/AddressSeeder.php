<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Address;
use App\Models\Profile;
use App\Models\City;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = Profile::all();
        
        if ($profiles->isEmpty()) {
            $this->command->warn('No hay perfiles para crear direcciones.');
            return;
        }
        
        foreach ($profiles->take(15) as $profile) {
            // Crear dirección predeterminada (casa)
            Address::factory()->default()->create([
                'profile_id' => $profile->id,
                'is_default' => true,
            ]);
            
            // Crear dirección de entrega adicional (opcional)
            if (rand(0, 1)) {
                Address::factory()->create([
                    'profile_id' => $profile->id,
                    'is_default' => false,
                ]);
            }
        }
        
        $this->command->info('AddressSeeder ejecutado exitosamente.');
    }
}
