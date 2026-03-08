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
     * Teléfono de la empresa: preferencia por phones con context=delivery_company y delivery_company_id=this;
     * si no hay, fallback al teléfono principal personal del perfil.
     */
    public function getPhoneAttribute(): ?string
    {
        $companyPhone = \App\Models\Phone::where('profile_id', $this->profile_id)
            ->where('context', \App\Models\Phone::CONTEXT_DELIVERY_COMPANY)
            ->where('delivery_company_id', $this->id)
            ->where('status', true)
            ->orderByDesc('is_primary')
            ->first();
        return $companyPhone?->full_number ?? $this->profile?->phone;
    }

    /**
     * Teléfonos de la empresa (context=delivery_company, delivery_company_id=this).
     */
    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    public function deliveryAgents()
    {
        return $this->hasMany(DeliveryAgent::class, 'company_id');
    }

    /**
     * Métodos de pago de la empresa (para recibir pagos/comisiones).
     */
    public function paymentMethods()
    {
        return $this->morphMany(\App\Models\PaymentMethod::class, 'payable');
    }
}
