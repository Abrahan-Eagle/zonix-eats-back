<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductExtra;
use Illuminate\Database\Seeder;

class ProductExtraSeeder extends Seeder
{
    /**
     * Crea extras por producto desde datos de BD.
     * Cada producto recibe 2-5 extras aleatorios.
     */
    public function run(): void
    {
        $extrasPool = [
            ['name' => 'Extra Queso Cheddar', 'price' => 1.00],
            ['name' => 'Doble Carne', 'price' => 3.50],
            ['name' => 'Tocino Extra', 'price' => 1.50],
            ['name' => 'Queso Mozzarella', 'price' => 2.00],
            ['name' => 'Guacamole', 'price' => 2.50],
            ['name' => 'Huevo', 'price' => 1.00],
            ['name' => 'Champiñones', 'price' => 1.25],
            ['name' => 'Jalapeños', 'price' => 0.75],
            ['name' => 'Bacon', 'price' => 2.00],
            ['name' => 'Extra Salsa', 'price' => 0.50],
        ];

        $products = Product::all();
        $count = 0;

        foreach ($products as $product) {
            $numExtras = rand(2, 5);
            $selected = collect($extrasPool)->random(min($numExtras, count($extrasPool)));
            $sortOrder = 0;
            foreach ($selected as $extra) {
                ProductExtra::create([
                    'product_id' => $product->id,
                    'name' => $extra['name'],
                    'price' => $extra['price'],
                    'sort_order' => $sortOrder++,
                ]);
                $count++;
            }
        }

        $this->command->info("ProductExtraSeeder: {$count} extras creados para {$products->count()} productos.");
    }
}
