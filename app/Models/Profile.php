<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Profile: almacena información extendida de los usuarios (datos personales, empresa, etc.).
 * Relacionado con User y otras entidades.
 */

class Profile extends Model
{
    use HasFactory;

    protected $table = 'profiles';

    // Definir los campos que se pueden llenar de forma masiva
    protected $fillable = [
        'user_id',
        'firstName',
        'middleName',
        'lastName',
        'secondLastName',
        'photo_users',
        'date_of_birth',
        'maritalStatus',
        'sex',
        'status',
        'address',
        'fcm_device_token',
        'notification_preferences'
        // Nota: business_name, business_type, tax_id están en Commerce
        // Nota: vehicle_type, license_number están en DeliveryAgent
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'status' => 'string',
        'notification_preferences' => 'array',
    ];


    /**
     * Relación con ubicaciones del usuario
     */
    public function userLocations()
    {
        return $this->hasMany(UserLocation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el carrito del comprador (carrito asociado al perfil, no al user).
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    // Relaciones con otros modelos
    /** @return \Illuminate\Database\Eloquent\Relations\HasMany */
    public function commerces()
    {
        return $this->hasMany(Commerce::class);
    }

    /**
     * Comercio principal (para compatibilidad y multi-restaurante).
     * Retorna el marcado is_primary o el primero si no hay.
     */
    public function commerce()
    {
        return $this->hasOne(Commerce::class)->where('is_primary', true);
    }

    /**
     * Obtener el comercio principal (o el único). Para código que usa $profile->commerce como modelo.
     */
    public function getPrimaryCommerce(): ?Commerce
    {
        return $this->commerces()->where('is_primary', true)->first()
            ?? $this->commerces()->orderBy('id')->first();
    }

    public function deliveryCompany()
    {
        return $this->hasOne(DeliveryCompany::class);
    }

    public function deliveryAgent()
    {
        return $this->hasOne(DeliveryAgent::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function postLikes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }





    /**
     * Relación uno a muchos con el modelo Address
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Relación con teléfonos
     */
    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    /**
     * Número de teléfono principal (desde tabla phones).
     * Compatibilidad: reemplaza el antiguo campo profile.phone.
     */
    public function getPhoneAttribute(): ?string
    {
        $primary = $this->phones()->where('is_primary', true)->where('status', true)->first();
        return $primary ? $primary->full_number : null;
    }

    /**
     * Relación con documentos
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Relación con notificaciones
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Relación con cupones asignados
     */
    public function assignedCoupons()
    {
        return $this->hasMany(Coupon::class, 'assigned_to_profile_id');
    }

    /**
     * Relación con usos de cupones
     */
    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class);
    }
}
