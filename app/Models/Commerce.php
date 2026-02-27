<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Address;
use App\Models\BusinessType;

class Commerce extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'is_primary',
        'business_type_id',
        'business_name',
        'business_type',
        'tax_id',
        'image',
        'address',
        'open',
        'schedule',
        'membership_type',
        'membership_monthly_fee',
        'membership_expires_at',
        'commission_percentage',
        'cancellation_count',
        'last_cancellation_date'
    ];

    protected $appends = ['phone'];

    protected $casts = [
        'is_primary' => 'boolean',
        'open' => 'boolean',
        'schedule' => 'array',
        'membership_monthly_fee' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'membership_expires_at' => 'datetime',
        'last_cancellation_date' => 'datetime',
        'cancellation_count' => 'integer'
    ];

    /**
     * Relación con el perfil
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Teléfono del comercio (desde perfil → tabla phones). Una sola fuente de verdad.
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->profile?->phone;
    }

    /**
     * Relación con el usuario a través del perfil
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, Profile::class);
    }

    /**
     * Relación con los productos
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relación con las órdenes
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relación con métodos de pago
     */
    public function paymentMethods()
    {
        return $this->morphMany(PaymentMethod::class, 'payable');
    }

    /**
     * Relación con facturas mensuales
     */
    public function invoices()
    {
        return $this->hasMany(CommerceInvoice::class);
    }

    /**
     * Relación con posts sociales
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Relación con categorías a través de productos
     */
    public function categories()
    {
        return $this->hasManyThrough(Category::class, Product::class);
    }

    /**
     * Dirección(es) del establecimiento (tabla addresses, role = commerce)
     */
    public function addresses()
    {
        return $this->hasMany(Address::class, 'commerce_id')
            ->where('role', 'commerce');
    }

    /**
     * Tipo de negocio (tabla business_types)
     */
    public function businessTypeRelation()
    {
        return $this->belongsTo(BusinessType::class, 'business_type_id');
    }
}
