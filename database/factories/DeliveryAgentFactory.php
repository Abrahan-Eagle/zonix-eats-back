<?php

namespace Database\Factories;

use App\Models\DeliveryCompany;
use App\Models\Profile;
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
        $working = $this->faker->boolean(60);
        $status = $working ? 'activo' : $this->faker->randomElement(['inactivo', 'suspendido']);
        
        return [
            'company_id' => $this->faker->optional(0.5)->passthrough(DeliveryCompany::factory()), // 50% independientes
            'profile_id' => Profile::factory(),
            'status' => $status,
            'working' => $working,
            'rating' => $this->faker->randomFloat(2, 3.5, 5),
            'vehicle_type' => $this->faker->randomElement(['motorcycle', 'car', 'bicycle', 'truck']),
            'license_number' => $this->faker->bothify('LIC-#######'),
            'current_latitude' => $working ? $this->faker->latitude(10.0, 10.5) : null,
            'current_longitude' => $working ? $this->faker->longitude(-67.0, -66.5) : null,
            'last_location_update' => $working ? $this->faker->dateTimeBetween('-1 hour', 'now') : null,
            'rejection_count' => $this->faker->numberBetween(0, 3),
            'last_rejection_date' => $this->faker->optional(0.2)->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
