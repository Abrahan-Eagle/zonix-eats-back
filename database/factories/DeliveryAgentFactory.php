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
            'company_id' => DeliveryCompany::factory(),
            'user_id' => \App\Models\User::factory(),
            'estado' => 'activo',
        ];
    }
}
