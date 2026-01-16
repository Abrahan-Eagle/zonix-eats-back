<?php

namespace Database\Factories;

use App\Models\DeliveryPayment;
use App\Models\Order;
use App\Models\DeliveryAgent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryPayment>
 */
class DeliveryPaymentFactory extends Factory
{
    protected $model = DeliveryPayment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending_payment_to_delivery', 'paid_to_delivery']);
        
        return [
            'order_id' => Order::factory(),
            'delivery_agent_id' => DeliveryAgent::factory(),
            'amount' => $this->faker->randomFloat(2, 5, 50), // 100% del delivery_fee
            'status' => $status,
            'paid_at' => $status === 'paid_to_delivery' ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
            'notes' => $this->faker->optional(0.2)->sentence(),
        ];
    }

    /**
     * Indicate that the payment is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid_to_delivery',
            'paid_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_payment_to_delivery',
            'paid_at' => null,
        ]);
    }
}
