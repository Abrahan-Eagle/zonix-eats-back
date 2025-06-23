<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    protected $fillable = [
        'commerce_id', 'nombre', 'descripcion', 'precio', 'imagen', 'disponible'
    ];

    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }

    /**
     * Relación muchos a muchos con órdenes (pivot: quantity).
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items', 'product_id', 'order_id')
            ->withPivot('quantity');
    }
}
