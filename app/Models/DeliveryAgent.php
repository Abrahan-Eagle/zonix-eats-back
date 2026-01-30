<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'profile_id',
        'status',
        'working',
        'rating',
        'vehicle_type',
        'license_number',
        'current_latitude',
        'current_longitude',
        'last_location_update',
        'rejection_count',
        'last_rejection_date'
    ];

    protected $appends = ['phone'];

    protected $casts = [
        'working' => 'boolean',
        'rating' => 'decimal:2',
        'current_latitude' => 'decimal:7',
        'current_longitude' => 'decimal:7',
        'last_location_update' => 'datetime',
        'last_rejection_date' => 'datetime',
        'rejection_count' => 'integer'
    ];

    public function company()
    {
        return $this->belongsTo(DeliveryCompany::class, 'company_id');
    }

    /**
     * Verificar si el motorizado es independiente (no pertenece a empresa)
     */
    public function isIndependent(): bool
    {
        return $this->company_id === null;
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Teléfono del repartidor (desde perfil → tabla phones). Una sola fuente de verdad.
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->profile?->phone;
    }

    public function user()
    {
        return $this->profile->user();
    }

    public function orderDeliveries()
    {
        return $this->hasMany(OrderDelivery::class);
    }

    public function paymentMethods()
    {
        return $this->morphMany(PaymentMethod::class, 'payable');
    }

    /**
     * Relación con pagos recibidos
     */
    public function payments()
    {
        return $this->hasMany(DeliveryPayment::class);
    }

    /**
     * Relación con reviews/calificaciones recibidas
     */
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}
