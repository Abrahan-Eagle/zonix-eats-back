<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\Profile;

class DeliveryAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = DeliveryCompany::all();
        
        // Crear agentes para empresas
        foreach ($companies as $company) {
            for ($i = 0; $i < 3; $i++) {
                $profile = Profile::factory()->create();
                $profile->user->update(['role' => 'delivery']);
                
                DeliveryAgent::factory()->create([
                    'company_id' => $company->id,
                    'profile_id' => $profile->id,
                ]);
            }
        }
        
        // Crear algunos agentes independientes
        for ($i = 0; $i < 5; $i++) {
            $profile = Profile::factory()->create();
            $profile->user->update(['role' => 'delivery']);
            
            DeliveryAgent::factory()->create([
                'company_id' => null, // Independiente
                'profile_id' => $profile->id,
            ]);
        }
        
        $this->command->info('DeliveryAgentSeeder ejecutado exitosamente.');
    }
}
