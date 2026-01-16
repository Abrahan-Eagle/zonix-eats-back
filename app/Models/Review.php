<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'order_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'comment'
    ];

    protected $casts = [
        'rating' => 'integer'
    ];

    public function reviewable()
    {
        return $this->morphTo();
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Relación con la orden (para validar que se califica después de orden entregada)
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
