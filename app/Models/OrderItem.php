<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Modelo pivot para la relación muchos a muchos entre órdenes y productos.
     * Campos: order_id, product_id, cantidad, precio_unitario
     */

     protected $fillable = ['order_id', 'product_id', 'cantidad', 'precio_unitario'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
