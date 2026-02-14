<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductExtra>
 */
class ProductExtraFactory extends Factory
{
    public function definition(): array
    {
        $extras = [
            ['name' => 'Extra Queso Cheddar', 'price' => 1.00],
            ['name' => 'Doble Carne', 'price' => 3.50],
            ['name' => 'Tocino Extra', 'price' => 1.50],
            ['name' => 'Queso Mozzarella', 'price' => 2.00],
            ['name' => 'Guacamole', 'price' => 2.50],
            ['name' => 'Huevo', 'price' => 1.00],
            ['name' => 'Champiñones', 'price' => 1.25],
            ['name' => 'Jalapeños', 'price' => 0.75],
        ];

        $extra = fake()->randomElement($extras);

        return [
            'product_id' => Product::factory(),
            'name' => $extra['name'],
            'price' => $extra['price'],
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
