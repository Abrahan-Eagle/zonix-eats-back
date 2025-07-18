<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_agent_id',
        'bank_id',
        'type',
        'brand',
        'account_number',
        'phone',
        'owner_name',
        'owner_id',
        'is_default',
        'is_active',
    ];

    public function deliveryAgent()
    {
        return $this->belongsTo(DeliveryAgent::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
} 