<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'commerce_id',
        'delivery_company_id',
        'delivery_type',
        'status',
        'approved_for_payment',
        'approved_for_payment_at',
        'total',
        'delivery_fee',
        'delivery_payment_amount',
        'commission_amount',
        'cancellation_penalty',
        'cancelled_by',
        'estimated_delivery_time',
        'receipt_url',
        'payment_proof',
        'payment_method',
        'reference_number',
        'payment_validated_at',
        'payment_proof_uploaded_at',
        'cancellation_reason',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'notes',
        'agent_accepted_at',
        'pickup_token',
        'delivery_token',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'delivery_payment_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'cancellation_penalty' => 'decimal:2',
        'estimated_delivery_time' => 'integer',
        'approved_for_payment' => 'boolean',
        'approved_for_payment_at' => 'datetime',
        'payment_validated_at' => 'datetime',
        'payment_proof_uploaded_at' => 'datetime',
        'agent_accepted_at' => 'datetime',
    ];

    /**
     * Relación con el perfil
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Relación con el usuario a través del perfil
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, Profile::class);
    }

    /**
     * Relación con el comercio
     */
    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }

    /**
     * Empresa de delivery asignada a la orden (para asignación de repartidor).
     */
    public function deliveryCompany()
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id');
    }

    /**
     * Relación con los productos a través de order_items
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps();
    }

    /**
     * Relación con los items de orden
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relación con la entrega de la orden
     */
    public function orderDelivery()
    {
        return $this->hasOne(OrderDelivery::class);
    }

    /**
     * Repartidor asignado (vía orderDelivery). Para compatibilidad con controladores que usan $order->deliveryAgent.
     * hasOneThrough: Order -> OrderDelivery (order_id) -> DeliveryAgent (agent_id -> id).
     */
    public function deliveryAgent()
    {
        return $this->hasOneThrough(
            DeliveryAgent::class,
            OrderDelivery::class,
            'order_id',   // FK en order_delivery hacia orders
            'agent_id',   // FK en order_delivery hacia delivery_agents
            'id',         // local key en orders
            'id'          // local key en delivery_agents
        );
    }

    public function delivery()
    {
        return $this->hasOne(\App\Models\OrderDelivery::class);
    }

    public function items()
    {
        return $this->hasMany(\App\Models\OrderItem::class);
    }

    /**
     * Relación con mensajes de chat
     */
    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Relación con disputas/quejas
     */
    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    /**
     * Relación con pagos a delivery
     */
    public function deliveryPayments()
    {
        return $this->hasMany(DeliveryPayment::class);
    }

    /**
     * Relación con reviews/calificaciones
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderPayments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function foodPayment()
    {
        return $this->hasOne(OrderPayment::class)->where('type', 'food');
    }

    public function deliveryPayment()
    {
        return $this->hasOne(OrderPayment::class)->where('type', 'delivery');
    }

    /**
     * Verifica si todos los pagos requeridos están validados.
     * Si no hay order_payments (órdenes legacy), se considera validado al validar desde commerce.
     * Pickup: solo food. Delivery: food + delivery.
     */
    public function allPaymentsValidated(): bool
    {
        $payments = $this->orderPayments;
        if ($payments->isEmpty()) {
            return true; // Legacy: sin order_payments, el commerce valida directamente
        }
        $food = $payments->firstWhere('type', 'food');
        if ($food && ! $food->isValidated()) {
            return false;
        }
        if ($this->delivery_type === 'delivery') {
            $delivery = $payments->firstWhere('type', 'delivery');
            if ($delivery && ! $delivery->isValidated()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sin comprobante a la espera de decisión del comercio: ni columnas legacy en `orders` ni filas en `order_payments`.
     * Usado por el comando de expiración cuando `skip_if_proof_pending` está activo.
     */
    public function scopeWithoutAwaitingProofValidation($query)
    {
        return $query
            ->where(function ($legacy) {
                $legacy->whereNull('payment_proof')
                    ->orWhereNotNull('payment_validated_at');
            })
            ->whereDoesntHave('orderPayments', function ($q) {
                $q->awaitingCommerceValidation();
            });
    }

    /**
     * Reglas de vencimiento por TTL para órdenes pending_payment (edad desde creación y/o desde approved_for_payment_at).
     */
    public function scopeWherePendingPaymentTtlExceeded($query, int $maxAgeMinutes, int $afterApprovalMinutes)
    {
        return $query->where(function ($q) use ($maxAgeMinutes, $afterApprovalMinutes) {
            if ($maxAgeMinutes > 0 && $afterApprovalMinutes > 0) {
                $q->where('created_at', '<', now()->subMinutes($maxAgeMinutes))
                    ->orWhere(function ($q2) use ($afterApprovalMinutes) {
                        $q2->where('approved_for_payment', true)
                            ->whereNotNull('approved_for_payment_at')
                            ->where('approved_for_payment_at', '<', now()->subMinutes($afterApprovalMinutes));
                    });
            } elseif ($maxAgeMinutes > 0) {
                $q->where('created_at', '<', now()->subMinutes($maxAgeMinutes));
            } else {
                $q->where('approved_for_payment', true)
                    ->whereNotNull('approved_for_payment_at')
                    ->where('approved_for_payment_at', '<', now()->subMinutes($afterApprovalMinutes));
            }
        });
    }
}
