<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $discountType = $this->faker->randomElement(['percentage', 'fixed']);
        $discountValue = $discountType === 'percentage' 
            ? $this->faker->numberBetween(5, 50) 
            : $this->faker->randomFloat(2, 5, 20);
        
        $startDate = $this->faker->dateTimeBetween('-1 week', '+1 week');
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 month');
        
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(), // Siempre generar descripción (no es nullable)
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'minimum_order' => $this->faker->optional(0.6)->randomFloat(2, 10, 50) ?? 0, // Default 0 si es null
            'maximum_discount' => $discountType === 'percentage' 
                ? $this->faker->optional(0.5)->randomFloat(2, 10, 50) 
                : null,
            'image_url' => $this->faker->optional(0.7)->imageUrl(),
            'banner_url' => $this->faker->optional(0.5)->imageUrl(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'terms_conditions' => $this->faker->optional(0.5)->paragraph(),
            'priority' => $this->faker->numberBetween(1, 10),
            'is_active' => $this->faker->boolean(70),
        ];
    }

    /**
     * Indicate that the promotion is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'start_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 month'),
        ]);
    }
}
