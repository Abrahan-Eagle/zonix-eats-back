<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo DeliveryPayment: trackea pagos a delivery según modelo de negocio.
 * El delivery recibe 100% del delivery_fee que pagó el cliente.
 */
class DeliveryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'delivery_agent_id',
        'amount',
        'status',
        'paid_at',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime'
    ];

    /**
     * Relación con la orden
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relación con el agente de delivery
     */
    public function deliveryAgent()
    {
        return $this->belongsTo(DeliveryAgent::class);
    }
}
