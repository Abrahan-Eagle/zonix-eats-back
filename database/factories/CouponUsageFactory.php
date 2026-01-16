<?php

namespace Database\Factories;

use App\Models\CouponUsage;
use App\Models\Coupon;
use App\Models\Profile;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CouponUsage>
 */
class CouponUsageFactory extends Factory
{
    protected $model = CouponUsage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coupon_id' => Coupon::factory(),
            'profile_id' => Profile::factory(),
            'order_id' => Order::factory(),
            'discount_amount' => $this->faker->randomFloat(2, 2, 20),
            'used_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
