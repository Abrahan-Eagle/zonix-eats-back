<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Dispute: gestiona quejas/disputas entre cliente-comercio-delivery.
 * Sistema de tickets según modelo de negocio.
 */
class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reported_by_type',
        'reported_by_id',
        'reported_against_type',
        'reported_against_id',
        'type',
        'description',
        'status',
        'admin_notes',
        'resolved_at'
    ];

    protected $casts = [
        'resolved_at' => 'datetime'
    ];

    /**
     * Relación con la orden
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relación polimórfica: quién reporta (puede ser user, commerce o delivery)
     */
    public function reportedBy()
    {
        return $this->morphTo('reported_by');
    }

    /**
     * Relación polimórfica: contra quién se reporta (puede ser user, commerce o delivery)
     */
    public function reportedAgainst()
    {
        return $this->morphTo('reported_against');
    }
}
