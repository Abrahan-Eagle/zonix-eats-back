<?php

namespace App\Policies;

use App\Models\OpticalPartner;
use App\Models\PatientProfile;
use App\Models\User;

class PatientProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'optical_partner'], true);
    }

    public function view(User $user, PatientProfile $patientProfile): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'optical_partner') {
            $partner = $user->primaryOpticalPartner();

            return $partner && (int) $patientProfile->optical_partner_id === (int) $partner->id;
        }

        if ($user->role === 'user') {
            return $user->profile
                && (int) $user->profile->patientProfile?->id === (int) $patientProfile->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'optical_partner'], true);
    }
}
