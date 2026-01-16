<?php

namespace Database\Factories;

use App\Models\UserLocation;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserLocation>
 */
class UserLocationFactory extends Factory
{
    protected $model = UserLocation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'latitude' => $this->faker->latitude(10.0, 10.5), // Coordenadas de Venezuela (Caracas)
            'longitude' => $this->faker->longitude(-67.0, -66.5),
            'accuracy' => $this->faker->optional(0.7)->randomFloat(2, 5, 50), // metros
            'altitude' => $this->faker->optional(0.5)->randomFloat(2, 800, 1200), // metros sobre el nivel del mar
            'speed' => $this->faker->optional(0.3)->randomFloat(2, 0, 60), // km/h
            'heading' => $this->faker->optional(0.3)->randomFloat(2, 0, 360), // grados
            'address' => $this->faker->optional(0.8)->address(),
            'recorded_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
