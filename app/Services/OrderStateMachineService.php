<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderStateMachineService
{
    public const ERROR_INVALID_TRANSITION = 'ORDER_INVALID_TRANSITION';

    public const ERROR_INVALID_STATUS = 'ORDER_INVALID_STATUS';

    private const VALID_STATUSES = [
        'pending_payment',
        'paid',
        'processing',
        'shipped',
        'delivered',
        'cancelled',
    ];

    private const ALIASES = [
        'pending' => 'pending_payment',
        'confirmed' => 'paid',
        'preparing' => 'processing',
        'ready' => 'processing',
        'on_way' => 'shipped',
        'out_for_delivery' => 'shipped',
    ];

    private const TRANSITIONS = [
        'buyer' => [
            'pending_payment' => ['cancelled'],
        ],
        'commerce' => [
            'pending_payment' => ['paid', 'cancelled'],
            'paid' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
        ],
        'delivery' => [
            'shipped' => ['delivered'],
            'processing' => ['shipped'],
        ],
        'delivery_company' => [
            'pending_payment' => ['paid'],
        ],
        'admin' => [
            'pending_payment' => ['cancelled'],
            'paid' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
        ],
    ];

    public function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));

        return self::ALIASES[$status] ?? $status;
    }

    public function isValidStatus(string $status): bool
    {
        return in_array($status, self::VALID_STATUSES, true);
    }

    /**
     * @param  Order|null  $order  Requerido para reglas contextuales (p. ej. recogida en tienda).
     */
    public function canTransition(string $actorRole, string $fromStatus, string $toStatus, ?Order $order = null): array
    {
        $from = $this->normalizeStatus($fromStatus);
        $to = $this->normalizeStatus($toStatus);
        $role = strtolower(trim($actorRole));

        if (! $this->isValidStatus($from) || ! $this->isValidStatus($to)) {
            return [
                'allowed' => false,
                'http_status' => 422,
                'error_code' => self::ERROR_INVALID_STATUS,
                'message' => 'Estado de orden inválido.',
                'from' => $from,
                'to' => $to,
            ];
        }

        if ($from === $to) {
            return [
                'allowed' => true,
                'http_status' => 200,
                'error_code' => null,
                'message' => 'Transición idempotente.',
                'from' => $from,
                'to' => $to,
            ];
        }

        $allowedTargets = self::TRANSITIONS[$role][$from] ?? [];
        if (! in_array($to, $allowedTargets, true)) {
            if ($this->allowsCommercePickupDelivered($role, $from, $to, $order)) {
                return [
                    'allowed' => true,
                    'http_status' => 200,
                    'error_code' => null,
                    'message' => 'Transición permitida (recogida en tienda).',
                    'from' => $from,
                    'to' => $to,
                ];
            }

            return [
                'allowed' => false,
                'http_status' => 409,
                'error_code' => self::ERROR_INVALID_TRANSITION,
                'message' => "No se permite transición de '{$from}' a '{$to}' para rol '{$role}'.",
                'from' => $from,
                'to' => $to,
            ];
        }

        return [
            'allowed' => true,
            'http_status' => 200,
            'error_code' => null,
            'message' => 'Transición permitida.',
            'from' => $from,
            'to' => $to,
        ];
    }

    /**
     * El comercio puede pasar shipped → delivered solo para pedidos pickup (entrega en mostrador).
     */
    private function allowsCommercePickupDelivered(string $role, string $from, string $to, ?Order $order): bool
    {
        if ($role !== 'commerce' || $from !== 'shipped' || $to !== 'delivered') {
            return false;
        }
        if ($order === null) {
            return false;
        }

        return $order->delivery_type === 'pickup';
    }

    public function applyTransition(
        Order $order,
        string $actorRole,
        string $toStatus,
        ?int $actorId = null,
        string $source = 'api',
        ?string $reason = null
    ): array {
        $decision = $this->canTransition($actorRole, $order->status, $toStatus, $order);
        if (! $decision['allowed']) {
            Log::warning('order_transition_rejected', [
                'order_id' => $order->id,
                'actor_role' => $actorRole,
                'actor_id' => $actorId,
                'from_status' => $decision['from'],
                'to_status' => $decision['to'],
                'error_code' => $decision['error_code'],
                'reason' => $reason,
                'source' => $source,
            ]);

            return $decision;
        }

        $from = $this->normalizeStatus($order->status);
        $to = $this->normalizeStatus($toStatus);

        if ($from === $to) {
            return $decision;
        }

        DB::transaction(function () use ($order, $to, $actorRole, $actorId, $source, $reason, $from) {
            $order->update(['status' => $to]);

            DB::table('order_status_history')->insert([
                'order_id' => $order->id,
                'from_status' => $from,
                'to_status' => $to,
                'actor_role' => $actorRole,
                'actor_id' => $actorId,
                'source' => $source,
                'reason' => $reason,
                'occurred_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Log::info('order_transition_applied', [
            'order_id' => $order->id,
            'actor_role' => $actorRole,
            'actor_id' => $actorId,
            'from_status' => $from,
            'to_status' => $to,
            'source' => $source,
            'reason' => $reason,
        ]);

        return $decision;
    }
}
