<?php

namespace Database\Factories;

use App\Models\Commerce;
use App\Models\DeliveryCompany;
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
        $isPaidOrBeyond = in_array($status, ['paid', 'processing', 'shipped', 'delivered'], true);
        $approvedForPayment = $status === 'pending_payment' ? $this->faker->boolean(50) : true;

        return [
            'profile_id' => Profile::factory(),
            'commerce_id' => Commerce::factory(),
            'delivery_company_id' => null,
            'delivery_type' => $deliveryType,
            'status' => $status,
            'approved_for_payment' => $approvedForPayment,
            'approved_for_payment_at' => ($status === 'pending_payment' && $approvedForPayment)
                ? $this->faker->dateTimeBetween('-3 days', 'now')
                : null,
            'total' => $total,
            'delivery_fee' => $deliveryFee,
            'delivery_payment_amount' => $deliveryType === 'delivery' && in_array($status, ['shipped', 'delivered']) ? $deliveryFee : null,
            'commission_amount' => $this->faker->randomFloat(2, 0, $total * 0.1), // Hasta 10% de comisión
            'cancellation_penalty' => $status === 'cancelled' && $this->faker->boolean(30) ? $this->faker->randomFloat(2, 5, 20) : 0,
            'cancelled_by' => $status === 'cancelled' ? $this->faker->randomElement(['user_id', 'commerce_id', 'admin_id']) : null,
            'estimated_delivery_time' => $deliveryType === 'delivery' ? $this->faker->numberBetween(15, 60) : null,
            'receipt_url' => $isPaidOrBeyond ? $this->faker->optional()->url() : null,
            // Usar el status *final* (tras merge en create()), no el $status aleatorio de arriba — si no,
            // un override `status => pending_payment` podía dejar payment_proof no nulo y romper scopes (p. ej. expiración TTL).
            'payment_proof' => function (array $attributes) {
                $s = $attributes['status'] ?? 'pending_payment';

                return $s === 'pending_payment' ? null : ($this->faker->boolean(70) ? $this->faker->imageUrl() : null);
            },
            'payment_method' => function (array $attributes) {
                $s = $attributes['status'] ?? 'pending_payment';

                return $s !== 'pending_payment' ? $this->faker->randomElement(['cash', 'card', 'mobile_payment', 'bank_transfer']) : null;
            },
            'reference_number' => function (array $attributes) {
                $s = $attributes['status'] ?? 'pending_payment';

                return $s !== 'pending_payment' ? $this->faker->optional()->numerify('REF#######') : null;
            },
            'payment_validated_at' => function (array $attributes) {
                $s = $attributes['status'] ?? 'pending_payment';
                $paidOrBeyond = in_array($s, ['paid', 'processing', 'shipped', 'delivered'], true);

                return $paidOrBeyond ? $this->faker->dateTimeBetween('-1 week', 'now') : null;
            },
            'payment_proof_uploaded_at' => function (array $attributes) {
                $s = $attributes['status'] ?? 'pending_payment';

                return $s !== 'pending_payment' ? $this->faker->optional()->dateTimeBetween('-1 week', 'now') : null;
            },
            'cancellation_reason' => $status === 'cancelled' ? $this->faker->sentence() : null,
            'delivery_address' => $deliveryType === 'delivery' ? $this->faker->address() : null,
            'delivery_latitude' => $deliveryType === 'delivery' ? $this->faker->latitude(10.0, 10.2) : null,
            'delivery_longitude' => $deliveryType === 'delivery' ? $this->faker->longitude(-68.1, -67.9) : null,
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function independentDelivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_type' => 'delivery',
            'delivery_company_id' => null,
        ]);
    }

    public function companyDelivery(?int $deliveryCompanyId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_type' => 'delivery',
            'delivery_company_id' => $deliveryCompanyId ?? DeliveryCompany::factory(),
        ]);
    }
}
