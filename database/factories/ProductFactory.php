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
        return [
            'commerce_id' => Commerce::factory(),
            'nombre' => $this->faker->word,
            'descripcion' => $this->faker->sentence,
            'precio' => $this->faker->randomFloat(2, 1, 100),
            'imagen' => $this->faker->imageUrl(),
            'disponible' => true,
        ];
    }
}
