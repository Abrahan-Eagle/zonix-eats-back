<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear promociones activas
        Promotion::factory()->count(5)->active()->create();

        // Crear algunas promociones inactivas
        Promotion::factory()->count(3)->create([
            'is_active' => false,
        ]);

        $this->command->info('PromotionSeeder ejecutado exitosamente.');
    }
}
