<?php

namespace Database\Seeders;

use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class DeliveryAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = DeliveryCompany::all();

        // Crear agentes para empresas (rol delivery_agent)
        foreach ($companies as $company) {
            for ($i = 0; $i < 3; $i++) {
                $profile = Profile::factory()->create();
                $profile->user->update(['role' => 'delivery_agent']);

                DeliveryAgent::factory()->companyManaged($company->id)->create([
                    'company_id' => $company->id,
                    'profile_id' => $profile->id,
                ]);
            }
        }

        // Crear algunos agentes independientes (rol delivery - sin compañía)
        for ($i = 0; $i < 5; $i++) {
            $profile = Profile::factory()->create();
            $profile->user->update(['role' => 'delivery']);

            DeliveryAgent::factory()->independent()->create([
                'company_id' => null, // Independiente / autónomo
                'profile_id' => $profile->id,
            ]);
        }

        $this->command->info('DeliveryAgentSeeder ejecutado exitosamente.');
    }
}
