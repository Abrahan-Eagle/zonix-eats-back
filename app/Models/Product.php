<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'commerce_id',
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'available',
        'stock_quantity'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'available' => 'boolean',
        'stock_quantity' => 'integer'
    ];

    /**
     * Relación con el comercio
     */
    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }

    /**
     * Relación con la categoría
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relación con los items de orden
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relación con las órdenes a través de order_items
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items');
    }

    /**
     * Extras/addons con precio adicional (ej: Extra Queso, Doble Carne)
     */
    public function extras()
    {
        return $this->hasMany(ProductExtra::class)->orderBy('sort_order');
    }

    /**
     * Preferencias sin precio (ej: Sin Cebolla, Sin Tomate)
     */
    public function preferences()
    {
        return $this->hasMany(ProductPreference::class)->orderBy('sort_order');
    }
}
