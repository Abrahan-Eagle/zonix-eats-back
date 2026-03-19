<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserLocation;
use App\Models\Profile;

class UserLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = Profile::all();
        
        if ($profiles->isEmpty()) {
            $this->command->warn('No hay perfiles para crear ubicaciones.');
            return;
        }
        
        // Crear ubicaciones recientes para algunos perfiles
        foreach ($profiles->take(20) as $profile) {
            // Crear 1-3 ubicaciones por perfil
            UserLocation::factory()->count(rand(1, 3))->create([
                'profile_id' => $profile->id,
            ]);
        }
        
        $this->command->info('UserLocationSeeder ejecutado exitosamente.');
    }
}
