<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryObservabilitySnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'window_hours',
        'orders_total',
        'avg_assignment_minutes',
        'avg_delivery_minutes',
        'assignment_p50_minutes',
        'assignment_p95_minutes',
        'delivery_p50_minutes',
        'delivery_p95_minutes',
        'timeout_count',
        'timeout_ratio_percent',
        'agent_no_response_ratio_percent',
        'success_ratio_percent',
        'cancelled_ratio_percent',
        'unassigned_over_threshold',
        'frozen_tracking_count',
    ];
}
