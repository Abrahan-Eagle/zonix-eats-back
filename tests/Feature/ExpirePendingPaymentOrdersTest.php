<?php

namespace Tests\Feature;

use App\Models\Commerce;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\Concerns\InteractsWithTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExpirePendingPaymentOrdersTest extends TestCase
{
    use InteractsWithTime;
    use RefreshDatabase;

    private function applyExpireConfig(bool $enabled, int $maxAge, int $afterApproval, ?bool $skipIfProofPending = null): void
    {
        // Siempre las cuatro claves: evita estado heredado de otros tests o de .env local.
        config([
            'zonix.expire_pending_payment.enabled' => $enabled,
            'zonix.expire_pending_payment.max_age_minutes' => $maxAge,
            'zonix.expire_pending_payment.after_approval_minutes' => $afterApproval,
            'zonix.expire_pending_payment.skip_if_proof_pending' => $skipIfProofPending ?? true,
        ]);
    }

    public function test_command_cancels_pending_payment_when_max_age_exceeded(): void
    {
        $this->applyExpireConfig(true, 60, 0);

        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id]);
        $order = Order::factory()->create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'status' => 'pending_payment',
            'approved_for_payment' => false,
        ]);
        $order->forceFill(['created_at' => now()->subHours(2)])->save();

        Artisan::call('zonix:expire-pending-payment-orders');

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('system', $order->cancelled_by);
        $this->assertNotNull($order->cancellation_reason);
    }

    public function test_command_cancels_when_after_approval_ttl_exceeded(): void
    {
        // Reloj fijo: evita carreras donde `now()` al crear la orden y al ejecutar el comando
        // difieren lo suficiente para que el TTL "tras aprobación" deje de cumplirse en SQLite.
        $this->travelTo(Carbon::parse('2026-04-14 15:00:00'));
        try {
            $this->applyExpireConfig(true, 0, 10);

            $user = User::factory()->create(['role' => 'users']);
            $profile = Profile::factory()->create(['user_id' => $user->id]);
            $commerce = Commerce::factory()->create(['profile_id' => $profile->id]);
            $order = Order::factory()->create([
                'profile_id' => $profile->id,
                'commerce_id' => $commerce->id,
                'status' => 'pending_payment',
                'approved_for_payment' => true,
                'approved_for_payment_at' => now()->subHour(),
            ]);
            $order->forceFill(['created_at' => now()->subMinutes(5)])->save();

            Artisan::call('zonix:expire-pending-payment-orders');

            $order->refresh();
            $this->assertSame('cancelled', $order->status);
            $this->assertSame('system', $order->cancelled_by);
        } finally {
            $this->travelBack();
        }
    }

    public function test_command_does_not_touch_paid_orders(): void
    {
        $this->applyExpireConfig(true, 1, 1);

        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id]);
        $order = Order::factory()->create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'status' => 'paid',
        ]);
        $order->forceFill(['created_at' => now()->subDays(30)])->save();

        Artisan::call('zonix:expire-pending-payment-orders');

        $order->refresh();
        $this->assertSame('paid', $order->status);
    }

    public function test_command_restores_stock_when_expiring(): void
    {
        $this->applyExpireConfig(true, 30, 0);

        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => true,
            'stock_quantity' => 7,
        ]);
        $order = Order::factory()->create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'status' => 'pending_payment',
            'approved_for_payment' => false,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => $product->price,
        ]);
        // Igual que en checkout: el stock se reserva al crear el pedido (las factories no lo hacen solas).
        $product->decrement('stock_quantity', 3);
        $order->forceFill(['created_at' => now()->subHour()])->save();

        Artisan::call('zonix:expire-pending-payment-orders');

        $product->refresh();
        $this->assertSame(7, (int) $product->stock_quantity);
        $order->refresh();
        $this->assertSame('cancelled', $order->status);
    }

    public function test_dry_run_does_not_cancel(): void
    {
        $this->applyExpireConfig(true, 60, 0);

        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id]);
        $order = Order::factory()->create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'status' => 'pending_payment',
        ]);
        $order->forceFill(['created_at' => now()->subHours(2)])->save();

        Artisan::call('zonix:expire-pending-payment-orders', ['--dry-run' => true]);

        $order->refresh();
        $this->assertSame('pending_payment', $order->status);
    }

    public function test_command_does_not_cancel_when_order_payment_proof_pending(): void
    {
        $this->applyExpireConfig(true, 60, 0, true);

        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id]);
        $order = Order::factory()->create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'status' => 'pending_payment',
            'approved_for_payment' => false,
        ]);
        $order->forceFill(['created_at' => now()->subHours(2)])->save();

        OrderPayment::create([
            'order_id' => $order->id,
            'type' => 'food',
            'amount' => $order->total,
            'payee_type' => 'commerce',
            'payee_id' => $commerce->id,
            'payment_proof' => 'payment_proofs/test.png',
            'payment_proof_uploaded_at' => now(),
            'validated_at' => null,
            'rejected_at' => null,
        ]);

        Artisan::call('zonix:expire-pending-payment-orders');

        $order->refresh();
        $this->assertSame('pending_payment', $order->status);
    }

    public function test_command_does_not_cancel_when_legacy_payment_proof_pending(): void
    {
        $this->applyExpireConfig(true, 60, 0, true);

        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id]);
        $order = Order::factory()->create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'status' => 'pending_payment',
            'approved_for_payment' => false,
            'payment_proof' => 'payment_proofs/legacy.png',
            'payment_validated_at' => null,
        ]);
        $order->forceFill(['created_at' => now()->subHours(2)])->save();

        Artisan::call('zonix:expire-pending-payment-orders');

        $order->refresh();
        $this->assertSame('pending_payment', $order->status);
    }

    public function test_command_still_cancels_when_skip_proof_disabled(): void
    {
        $this->applyExpireConfig(true, 60, 0, false);

        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id]);
        $order = Order::factory()->create([
            'profile_id' => $profile->id,
            'commerce_id' => $commerce->id,
            'status' => 'pending_payment',
            'approved_for_payment' => false,
        ]);
        $order->forceFill(['created_at' => now()->subHours(2)])->save();

        OrderPayment::create([
            'order_id' => $order->id,
            'type' => 'food',
            'amount' => $order->total,
            'payee_type' => 'commerce',
            'payee_id' => $commerce->id,
            'payment_proof' => 'payment_proofs/test.png',
            'payment_proof_uploaded_at' => now(),
            'validated_at' => null,
            'rejected_at' => null,
        ]);

        Artisan::call('zonix:expire-pending-payment-orders');

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
    }
}
