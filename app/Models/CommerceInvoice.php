<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo CommerceInvoice: trackea facturas mensuales según modelo de negocio.
 * Cada factura incluye: membresía mensual + comisiones del mes.
 */
class CommerceInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'commerce_id',
        'membership_fee',
        'commission_amount',
        'total',
        'invoice_date',
        'due_date',
        'status',
        'paid_at',
        'notes'
    ];

    protected $casts = [
        'membership_fee' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime'
    ];

    /**
     * Relación con el comercio
     */
    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }
}
