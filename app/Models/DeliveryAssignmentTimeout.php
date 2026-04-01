<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAssignmentTimeout extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'company_id',
        'occurred_at',
        'source',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
