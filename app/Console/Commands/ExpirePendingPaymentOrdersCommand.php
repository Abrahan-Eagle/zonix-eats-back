<?php

namespace App\Console\Commands;

use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Services\OrderStateMachineService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ExpirePendingPaymentOrdersCommand extends Command
{
    protected $signature = 'zonix:expire-pending-payment-orders {--dry-run : Solo listar candidatos sin cancelar}';

    protected $description = 'Cancela órdenes en pending_payment que superan el plazo configurado (creación y/o tras aprobación del comercio). Omite órdenes con comprobante pendiente de validación si skip_if_proof_pending está activo.';

    public function handle(): int
    {
        if (! Schema::hasColumn('orders', 'approved_for_payment_at')) {
            $this->error(
                'Falta la columna orders.approved_for_payment_at. En bases ya migradas ejecute: '.
                'ALTER TABLE orders ADD COLUMN approved_for_payment_at TIMESTAMP NULL AFTER approved_for_payment;'
            );

            return self::FAILURE;
        }

        if (! config('zonix.expire_pending_payment.enabled', true)) {
            $this->info('expire_pending_payment está desactivado (ZONIX_EXPIRE_PENDING_PAYMENT_ENABLED).');

            return self::SUCCESS;
        }

        $maxAge = (int) config('zonix.expire_pending_payment.max_age_minutes', 0);
        $afterApproval = (int) config('zonix.expire_pending_payment.after_approval_minutes', 0);

        if ($maxAge <= 0 && $afterApproval <= 0) {
            $this->warn('Ambas reglas TTL están en 0; nada que hacer.');

            return self::SUCCESS;
        }

        $query = Order::query()
            ->where('status', 'pending_payment')
            ->when(config('zonix.expire_pending_payment.skip_if_proof_pending', true), function ($q) {
                $q->withoutAwaitingProofValidation();
            })
            ->wherePendingPaymentTtlExceeded($maxAge, $afterApproval)
            ->orderBy('id');

        $count = $query->count();
        if ($count === 0) {
            $this->info('No hay órdenes pending_payment vencidas.');

            return self::SUCCESS;
        }

        $this->info("Candidatos: {$count}");

        if ($this->option('dry-run')) {
            foreach ($query->cursor() as $order) {
                $this->line("  order_id={$order->id} created_at={$order->created_at} approved_for_payment_at=".($order->approved_for_payment_at ?? 'null'));
            }

            return self::SUCCESS;
        }

        $stateMachine = app(OrderStateMachineService::class);
        $cancelled = 0;

        $query->chunkById(50, function ($orders) use ($stateMachine, &$cancelled, $maxAge, $afterApproval) {
            foreach ($orders as $order) {
                $order->load('orderItems.product');
                $reason = $this->buildCancellationReason($order, $maxAge, $afterApproval);
                $source = 'system_expire_pending_payment';

                try {
                    DB::transaction(function () use ($order, $stateMachine, $reason, $source) {
                        foreach ($order->orderItems as $item) {
                            $product = $item->product;
                            if ($product && $product->stock_quantity !== null) {
                                $product->increment('stock_quantity', $item->quantity);
                                if ($product->stock_quantity > 0 && ! $product->available) {
                                    $product->update(['available' => true]);
                                }
                            }
                        }

                        $order->update([
                            'cancellation_reason' => $reason,
                            'cancelled_by' => 'system',
                        ]);

                        $decision = $stateMachine->applyTransition(
                            $order->fresh(),
                            'admin',
                            'cancelled',
                            null,
                            $source,
                            $reason
                        );

                        if (! ($decision['allowed'] ?? false)) {
                            throw new \RuntimeException($decision['message'] ?? 'Transición no permitida');
                        }
                    });

                    event(new OrderStatusChanged($order->fresh()));
                    $cancelled++;

                    Log::info('order_expired_pending_payment', [
                        'order_id' => $order->id,
                        'source' => $source,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('order_expire_pending_payment_failed', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        $this->info("Canceladas: {$cancelled}");

        return self::SUCCESS;
    }

    private function buildCancellationReason(Order $order, int $maxAge, int $afterApproval): string
    {
        $parts = [];
        if ($maxAge > 0 && $order->created_at && $order->created_at->lt(now()->subMinutes($maxAge))) {
            $parts[] = 'plazo máximo desde la creación del pedido';
        }
        if (
            $afterApproval > 0
            && $order->approved_for_payment
            && $order->approved_for_payment_at
            && $order->approved_for_payment_at->lt(now()->subMinutes($afterApproval))
        ) {
            $parts[] = 'plazo máximo tras la aprobación del comercio para pagar';
        }
        if ($parts === []) {
            return 'Pedido cancelado automáticamente por plazo de pago vencido.';
        }

        return 'Pedido cancelado automáticamente: '.implode(' y ', $parts).'.';
    }
}
