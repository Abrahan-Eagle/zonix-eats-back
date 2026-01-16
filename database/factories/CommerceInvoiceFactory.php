<?php

namespace Database\Factories;

use App\Models\CommerceInvoice;
use App\Models\Commerce;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommerceInvoice>
 */
class CommerceInvoiceFactory extends Factory
{
    protected $model = CommerceInvoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $membershipFee = $this->faker->randomFloat(2, 50, 200);
        $commissionAmount = $this->faker->randomFloat(2, 100, 1000);
        $total = $membershipFee + $commissionAmount;
        $status = $this->faker->randomElement(['pending', 'paid', 'overdue']);
        
        return [
            'commerce_id' => Commerce::factory(),
            'membership_fee' => $membershipFee,
            'commission_amount' => $commissionAmount,
            'total' => $total,
            'invoice_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => $status,
            'paid_at' => $status === 'paid' ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'notes' => $this->faker->optional(0.2)->sentence(),
        ];
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the invoice is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the invoice is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }
}
