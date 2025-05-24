<?php

namespace App\Models\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Commerce extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id', 'nombre_local', 'direccion', 'telefono',
        'pago_movil_banco', 'pago_movil_cedula', 'pago_movil_telefono'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
