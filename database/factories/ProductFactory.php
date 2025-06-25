<?php

namespace Database\Factories;

use App\Models\Commerce;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mealImages = [
            'https://www.themealdb.com/images/media/meals/wxywrq1468235067.jpg', // Apple Frangipan Tart
            'https://www.themealdb.com/images/media/meals/xvsurr1511719182.jpg', // Apple & Blackberry Crumble
            'https://www.themealdb.com/images/media/meals/adxcbq1619787919.jpg', // Apam balik
            'https://www.themealdb.com/images/media/meals/20z181619788503.jpg', // Ayam Percik
        ];
        return [
            'commerce_id' => Commerce::factory(),
            'nombre' => $this->faker->word,
            'descripcion' => $this->faker->sentence,
            'precio' => $this->faker->randomFloat(2, 1, 100),
            'imagen' => $this->faker->randomElement($mealImages),
            'disponible' => true,
        ];
    }

    public function withCommerce()
    {
        return $this->afterCreating(function ($product) {
            if (!$product->commerce_id) {
                $commerce = \App\Models\Commerce::factory()->create();
                $product->commerce_id = $commerce->id;
                $product->save();
            }
        });
    }
}
