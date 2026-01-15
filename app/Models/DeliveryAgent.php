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
        'phone',
        'current_latitude',
        'current_longitude',
        'last_location_update',
    ];

    protected $casts = [
        'working' => 'boolean',
        'rating' => 'decimal:2',
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
}
