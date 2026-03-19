<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductPreference;
use Illuminate\Database\Seeder;

class ProductPreferenceSeeder extends Seeder
{
    /**
     * Crea preferencias por producto desde datos de BD.
     * Cada producto recibe 1-4 preferencias aleatorias.
     */
    public function run(): void
    {
        $preferencesPool = [
            'Sin Cebolla',
            'Sin Tomate',
            'Sin Lechuga',
            'Bien cocido',
            'Poco cocido',
            'Sin picante',
            'Extra picante',
            'Salsa aparte',
            'Sin sal',
            'Sin gluten',
        ];

        $products = Product::all();
        $count = 0;

        foreach ($products as $product) {
            $numPrefs = rand(1, 4);
            $selected = collect($preferencesPool)->random(min($numPrefs, count($preferencesPool)));
            $selected = collect($selected)->values()->all();
            $sortOrder = 0;
            foreach ($selected as $name) {
                ProductPreference::create([
                    'product_id' => $product->id,
                    'name' => $name,
                    'sort_order' => $sortOrder++,
                ]);
                $count++;
            }
        }

        $this->command->info("ProductPreferenceSeeder: {$count} preferencias creadas para {$products->count()} productos.");
    }
}
