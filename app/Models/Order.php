<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'commerce_id',
        'tipo_entrega',
        'estado',
        'total',
        'comprobante_url',
        'notas',
        'delivery_id',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function user()
    {
        return $this->profile ? $this->profile->user() : null;
    }

    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery()
    {
        return $this->hasOne(OrderDelivery::class);
    }

    /**
     * Relación muchos a muchos con productos (pivot: quantity).
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id')
            ->withPivot('cantidad');
    }
}
