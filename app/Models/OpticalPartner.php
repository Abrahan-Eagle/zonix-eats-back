<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpticalPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'tax_id',
        'is_zonix_direct',
        'commission_rate',
        'status',
    ];

    protected $casts = [
        'is_zonix_direct' => 'boolean',
        'commission_rate' => 'decimal:4',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'optical_partner_users');
    }

    public function patientProfiles(): HasMany
    {
        return $this->hasMany(PatientProfile::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}
