<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'commerce_id',
        'type',
        'bank_id',
        'account_number',
        'phone',
        'is_active',
        'owner_name',
        'owner_id',
    ];

    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }
} 