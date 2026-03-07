<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DeliveryCompany;
use App\Models\Profile;
use App\Models\User;

class DeliveryCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear 3 empresas de delivery
        for ($i = 0; $i < 3; $i++) {
            $profile = Profile::factory()->create();
            $profile->user->update(['role' => 'delivery_company']);
            
            DeliveryCompany::factory()->create([
                'profile_id' => $profile->id,
            ]);
        }
        
        $this->command->info('DeliveryCompanySeeder ejecutado exitosamente.');
    }
}
