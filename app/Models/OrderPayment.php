<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'type',
        'amount',
        'payee_type',
        'payee_id',
        'payment_method_id',
        'payment_method_label',
        'reference_number',
        'payment_proof',
        'payment_proof_uploaded_at',
        'validated_at',
        'validated_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_proof_uploaded_at' => 'datetime',
        'validated_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Scope: comprobante subido y el comercio aún no valida ni rechaza (misma regla que {@see isPending()}).
     */
    public function scopeAwaitingCommerceValidation($query)
    {
        return $query->whereNotNull('payment_proof')
            ->whereNull('validated_at')
            ->whereNull('rejected_at');
    }

    public function isValidated(): bool
    {
        return $this->validated_at !== null;
    }

    public function isRejected(): bool
    {
        return $this->rejected_at !== null;
    }

    /** Comprobante presente y sin validar ni rechazar; coherente con {@see scopeAwaitingCommerceValidation()}. */
    public function isPending(): bool
    {
        return $this->payment_proof !== null && ! $this->isValidated() && ! $this->isRejected();
    }
}
