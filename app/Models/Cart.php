<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notes',
    ];

    /**
     * Relación con el usuario propietario del carrito
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los items del carrito
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Obtener o crear el carrito de un usuario
     */
    public static function getOrCreateForUser($userId)
    {
        return static::firstOrCreate(['user_id' => $userId]);
    }
}
