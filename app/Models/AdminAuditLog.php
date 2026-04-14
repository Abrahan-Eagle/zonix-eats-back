<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'method',
        'path',
        'entity_type',
        'entity_id',
        'status_code',
        'success',
        'ip_address',
        'user_agent',
        'payload',
    ];

    protected $casts = [
        'success' => 'boolean',
        'payload' => 'array',
    ];
}
