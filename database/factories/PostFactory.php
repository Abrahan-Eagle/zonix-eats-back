<?php

namespace Database\Factories;

use App\Models\Commerce;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
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
            'tipo' => $this->faker->randomElement(['foto', 'video']),
            'media_url' => $this->faker->imageUrl(),
            'name' => $this->faker->words(2, true),
            'price' => $this->faker->randomFloat(2, 5, 50),
            'descripcion' => $this->faker->sentence,
        ];
    }
}
