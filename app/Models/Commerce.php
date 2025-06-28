<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Commerce: representa un restaurante/comercio en la app de comida rápida.
 * Relacionado con perfil, productos, órdenes y publicaciones.
 */

class Commerce extends Model
{

    use HasFactory;

       protected $fillable = [
        'profile_id',
        'nombre_local',
        'imagen',
        'direccion',
        'telefono',
        'pago_movil_banco',
        'pago_movil_cedula',
        'pago_movil_telefono',
        'abierto',
        'horario'
    ];




     public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function user()
    {
        return $this->profile->user();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
