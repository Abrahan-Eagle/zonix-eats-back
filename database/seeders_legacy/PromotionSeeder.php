<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Promotion;

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
