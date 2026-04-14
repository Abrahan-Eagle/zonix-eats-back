<?php

namespace Database\Seeders;

use App\Models\DeliveryCompany;
use App\Models\Profile;
use Illuminate\Database\Seeder;

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
