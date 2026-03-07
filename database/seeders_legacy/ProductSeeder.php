<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Commerce;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Crea 5-8 productos por comercio, la mayoría disponibles.
     */
    public function run(): void
    {
        $commerces = Commerce::all();
        foreach ($commerces as $commerce) {
            Product::factory()->count(10)->create([
                'commerce_id' => $commerce->id,
                'available' => true,
            ]);
        }

        $this->command->info('ProductSeeder: ' . ($commerces->count() * 10) . ' productos creados (10 por comercio).');
    }
}
