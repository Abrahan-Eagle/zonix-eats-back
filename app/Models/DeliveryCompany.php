<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'name',
        'tax_id',
        'phone',
        'address',
        'image',
        'open',
        'schedule',
        'active',
    ];

    protected $casts = [
        'open' => 'boolean',
        'active' => 'boolean',
        'schedule' => 'array',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function deliveryAgents()
    {
        return $this->hasMany(DeliveryAgent::class, 'company_id');
    }
}
