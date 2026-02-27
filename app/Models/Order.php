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
        'delivery_type',
        'status',
        'approved_for_payment',
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
        'notes'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'delivery_payment_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'cancellation_penalty' => 'decimal:2',
        'estimated_delivery_time' => 'integer',
        'approved_for_payment' => 'boolean',
        'payment_validated_at' => 'datetime',
        'payment_proof_uploaded_at' => 'datetime'
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
}
