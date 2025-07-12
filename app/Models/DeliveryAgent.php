<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAgent extends Model
{
    use HasFactory;

     protected $fillable = [
        'company_id',
        'profile_id',
        'estado',
        'trabajando',
        'rating'
    ];

    protected $casts = [
        'trabajando' => 'boolean',
        'rating' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(DeliveryCompany::class, 'company_id');
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function user()
    {
        return $this->profile->user();
    }

    public function deliveries()
    {
        return $this->hasMany(OrderDelivery::class, 'agent_id');
    }
}
