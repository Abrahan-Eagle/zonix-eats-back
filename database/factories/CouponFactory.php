<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

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
        
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+3 months');
        
        return [
            'code' => strtoupper($this->faker->unique()->bothify('COUPON-####')),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional(0.8)->paragraph(),
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'minimum_order' => $this->faker->optional(0.6)->randomFloat(2, 10, 50),
            'maximum_discount' => $discountType === 'percentage' 
                ? $this->faker->optional(0.5)->randomFloat(2, 10, 50) 
                : null,
            'usage_limit' => $this->faker->optional(0.7)->numberBetween(10, 1000),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'terms_conditions' => $this->faker->optional(0.5)->paragraph(),
            'is_public' => $this->faker->boolean(70), // 70% son públicos
            'assigned_to_profile_id' => $this->faker->optional(0.3)->passthrough(Profile::factory()),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    /**
     * Indicate that the coupon is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the coupon is private (assigned to a profile).
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
            'assigned_to_profile_id' => Profile::factory(),
        ]);
    }
}
