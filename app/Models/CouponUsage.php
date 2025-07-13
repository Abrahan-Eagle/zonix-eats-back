<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'profile_id',
        'order_id',
        'discount_amount',
        'used_at'
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'used_at' => 'datetime'
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
} 