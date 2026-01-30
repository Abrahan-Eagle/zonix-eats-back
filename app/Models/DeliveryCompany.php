<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'name',
        'tax_id',
        'address',
        'image',
        'open',
        'schedule',
        'active',
    ];

    protected $appends = ['phone'];

    protected $casts = [
        'open' => 'boolean',
        'active' => 'boolean',
        'schedule' => 'array',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Teléfono de la empresa (desde perfil → tabla phones). Una sola fuente de verdad.
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->profile?->phone;
    }

    public function deliveryAgents()
    {
        return $this->hasMany(DeliveryAgent::class, 'company_id');
    }
}
