<?php

namespace Database\Factories;

use App\Models\DeliveryCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryAgent>
 */
class DeliveryAgentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => \App\Models\DeliveryCompany::factory(),
            'profile_id' => \App\Models\Profile::factory()->delivery(),
            'estado' => $this->faker->randomElement(['activo', 'inactivo', 'suspendido']),
            'trabajando' => $this->faker->boolean(60),
            'rating' => $this->faker->randomFloat(1, 1, 5),
        ];
    }
}
