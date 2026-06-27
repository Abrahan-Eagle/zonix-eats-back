<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Profile: información extendida del usuario (datos personales, contacto, notificaciones).
 */
class Profile extends Model
{
    use HasFactory;

    protected $table = 'profiles';

    protected $fillable = [
        'user_id',
        'firstName',
        'middleName',
        'lastName',
        'secondLastName',
        'photo_users',
        'date_of_birth',
        'maritalStatus',
        'sex',
        'status',
        'address',
        'fcm_device_token',
        'notification_preferences',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'status' => 'string',
        'notification_preferences' => 'array',
    ];

    protected $appends = ['phone'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    public function getPhoneAttribute(): ?string
    {
        $primary = $this->phones()
            ->where('context', Phone::CONTEXT_PERSONAL)
            ->where('is_primary', true)
            ->where('status', true)
            ->first();

        return $primary ? $primary->full_number : null;
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function patientProfile()
    {
        return $this->hasOne(PatientProfile::class);
    }
}
