<?php

namespace Database\Factories;

use App\Models\Commerce;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $deliveryType = $this->faker->randomElement(['pickup', 'delivery']);
        $status = $this->faker->randomElement(['pending_payment', 'paid', 'processing', 'shipped', 'delivered', 'cancelled']);
        $total = $this->faker->randomFloat(2, 10, 100);
        $deliveryFee = $deliveryType === 'delivery' ? $this->faker->randomFloat(2, 2, 15) : 0;
        
        return [
            'profile_id' => Profile::factory(),
            'commerce_id' => Commerce::factory(),
            'delivery_type' => $deliveryType,
            'status' => $status,
            'total' => $total,
            'delivery_fee' => $deliveryFee,
            'delivery_payment_amount' => $deliveryType === 'delivery' && in_array($status, ['shipped', 'delivered']) ? $deliveryFee : null,
            'commission_amount' => $this->faker->randomFloat(2, 0, $total * 0.1), // Hasta 10% de comisión
            'cancellation_penalty' => $status === 'cancelled' && $this->faker->boolean(30) ? $this->faker->randomFloat(2, 5, 20) : 0,
            'cancelled_by' => $status === 'cancelled' ? $this->faker->randomElement(['user_id', 'commerce_id', 'admin_id']) : null,
            'estimated_delivery_time' => $deliveryType === 'delivery' ? $this->faker->numberBetween(15, 60) : null,
            'receipt_url' => in_array($status, ['paid', 'processing', 'shipped', 'delivered']) ? $this->faker->optional()->url() : null,
            'payment_proof' => $status === 'pending_payment' ? null : ($this->faker->boolean(70) ? $this->faker->imageUrl() : null),
            'payment_method' => $status !== 'pending_payment' ? $this->faker->randomElement(['cash', 'card', 'mobile_payment', 'bank_transfer']) : null,
            'reference_number' => $status !== 'pending_payment' ? $this->faker->optional()->numerify('REF#######') : null,
            'payment_validated_at' => in_array($status, ['paid', 'processing', 'shipped', 'delivered']) ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
            'payment_proof_uploaded_at' => $status !== 'pending_payment' ? $this->faker->optional()->dateTimeBetween('-1 week', 'now') : null,
            'cancellation_reason' => $status === 'cancelled' ? $this->faker->sentence() : null,
            'delivery_address' => $deliveryType === 'delivery' ? $this->faker->address() : null,
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
