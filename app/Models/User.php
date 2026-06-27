<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Modelo User: representa a los usuarios de la app (miembros, admin).
     */

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'given_name',
        'family_name',
        'profile_pic',
        'role',
        'completed_onboarding',
        'light',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->role === $role || $this->roles->contains('name', $role);
        }

        return $this->roles->contains($role);
    }

    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function getAllRoles()
    {
        $roles = collect([$this->role]);
        if ($this->roles->isNotEmpty()) {
            $roles = $roles->merge($this->roles->pluck('name'));
        }

        return $roles->unique()->values();
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function paymentMethods()
    {
        return $this->morphMany(PaymentMethod::class, 'payable');
    }

    public function opticalPartners()
    {
        return $this->belongsToMany(OpticalPartner::class, 'optical_partner_users');
    }

    public function primaryOpticalPartner(): ?OpticalPartner
    {
        return $this->opticalPartners()->first();
    }
}
