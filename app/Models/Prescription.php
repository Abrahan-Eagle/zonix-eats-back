<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PATIENT_CONFIRMED = 'patient_confirmed';

    public const STATUS_LOCKED_FOR_ORDER = 'locked_for_order';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SUPERSEDED = 'superseded';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_PATIENT_UPLOAD = 'patient_upload';

    public const SOURCE_OCR_AI = 'ocr_ai';

    protected $fillable = [
        'patient_profile_id',
        'optical_partner_id',
        'od_sphere',
        'od_cylinder',
        'od_axis',
        'oi_sphere',
        'oi_cylinder',
        'oi_axis',
        'addition',
        'pd',
        'pd_near',
        'source',
        'status',
        'document_id',
        'approved_by_user_id',
        'approved_at',
        'patient_confirmed_at',
        'locked_at',
        'rejection_reason',
    ];

    protected $casts = [
        'od_sphere' => 'decimal:2',
        'od_cylinder' => 'decimal:2',
        'oi_sphere' => 'decimal:2',
        'oi_cylinder' => 'decimal:2',
        'addition' => 'decimal:2',
        'pd' => 'decimal:1',
        'pd_near' => 'decimal:1',
        'approved_at' => 'datetime',
        'patient_confirmed_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function opticalPartner(): BelongsTo
    {
        return $this->belongsTo(OpticalPartner::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
