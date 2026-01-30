<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'notes',
    ];

    /**
     * Relación con el perfil propietario del carrito (solo profiles conectado a users; dominio va a profile).
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Relación con los items del carrito
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Obtener o crear el carrito de un perfil
     */
    public static function getOrCreateForProfile($profileId)
    {
        return static::firstOrCreate(['profile_id' => $profileId]);
    }
}
