<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\UserLocation;
use Illuminate\Database\Seeder;

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
