<?php

namespace App\Policies;

use App\Models\OpticalPartner;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\User;

class PrescriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'optical_partner', 'user'], true);
    }

    public function view(User $user, Prescription $prescription): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'optical_partner') {
            $partner = $user->primaryOpticalPartner();

            return $partner && (int) $prescription->optical_partner_id === (int) $partner->id;
        }

        if ($user->role === 'user') {
            $patientProfile = $user->profile?->patientProfile;

            return $patientProfile
                && (int) $prescription->patient_profile_id === (int) $patientProfile->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'optical_partner'], true);
    }

    public function approve(User $user, Prescription $prescription): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role !== 'optical_partner') {
            return false;
        }

        $partner = $user->primaryOpticalPartner();

        return $partner && (int) $prescription->optical_partner_id === (int) $partner->id;
    }

    public function confirm(User $user, Prescription $prescription): bool
    {
        if ($user->role !== 'user') {
            return false;
        }

        $patientProfile = $user->profile?->patientProfile;

        return $patientProfile
            && (int) $prescription->patient_profile_id === (int) $patientProfile->id
            && $prescription->status === Prescription::STATUS_APPROVED;
    }
}
