<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'commerce_id',
        'delivery_agent_id',
        'profile_id',
        'type',
        'rating',
        'comment',
        'photos'
    ];

    protected $casts = [
        'photos' => 'array',
        'rating' => 'integer'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }

    public function deliveryAgent()
    {
        return $this->belongsTo(DeliveryAgent::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
