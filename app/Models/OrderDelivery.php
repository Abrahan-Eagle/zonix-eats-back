<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDelivery extends Model
{
    use HasFactory;

      protected $table = 'order_delivery';

    protected $fillable = ['order_id', 'agent_id', 'estado_envio'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function agent()
    {
        return $this->belongsTo(DeliveryAgent::class, 'agent_id');
    }
}
