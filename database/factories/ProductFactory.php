<?php

namespace Database\Factories;

use App\Models\Commerce;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

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
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 5, 50),
            'image' => $this->faker->imageUrl(),
            'available' => $this->faker->boolean(80),
        ];
    }

    /**
     * Indicate that the product should be created with a commerce.
     */
    public function withCommerce()
    {
        return $this->afterCreating(function (Product $product) {
            $commerce = Commerce::factory()->create();
            $product->update(['commerce_id' => $commerce->id]);
        });
    }
}
