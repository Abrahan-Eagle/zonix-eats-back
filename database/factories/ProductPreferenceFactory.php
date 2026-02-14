<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductPreference>
 */
class ProductPreferenceFactory extends Factory
{
    public function definition(): array
    {
        $preferences = [
            'Sin Cebolla',
            'Sin Tomate',
            'Sin Lechuga',
            'Bien cocido',
            'Poco cocido',
            'Sin picante',
            'Extra picante',
            'Salsa aparte',
            'Sin sal',
        ];

        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement($preferences),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
