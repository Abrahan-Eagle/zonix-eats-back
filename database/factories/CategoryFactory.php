<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Comida Rápida',
            'Pizza',
            'Hamburguesas',
            'Sushi',
            'Mexicana',
            'Italiana',
            'China',
            'Vegetariana',
            'Postres',
            'Bebidas',
            'Desayunos',
            'Almuerzos',
            'Cenas',
            'Snacks',
            'Mariscos'
        ];

        return [
            'name' => $this->faker->randomElement($categories),
            'description' => $this->faker->optional(0.7)->sentence(),
        ];
    }
}
