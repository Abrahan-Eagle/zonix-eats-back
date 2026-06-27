<?php

namespace App\Services\Optical;

use App\Models\OpticalPartner;
use App\Models\PatientProfile;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PatientRegistrationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function registerUnderPartner(OpticalPartner $partner, array $data, User $createdBy): PatientProfile
    {
        return DB::transaction(function () use ($partner, $data, $createdBy) {
            $user = User::where('email', $data['email'])->first();

            if ($user && $user->profile?->patientProfile) {
                throw new \InvalidArgumentException('El paciente ya está registrado en el sistema');
            }

            if (! $user) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'user',
                    'completed_onboarding' => false,
                ]);
            }

            $profile = $user->profile ?? Profile::create([
                'user_id' => $user->id,
                'firstName' => $data['firstName'],
                'lastName' => $data['lastName'],
                'status' => 'incompleteData',
            ]);

            return PatientProfile::create([
                'profile_id' => $profile->id,
                'optical_partner_id' => $partner->id,
                'clinical_notes' => $data['clinical_notes'] ?? null,
                'created_by_user_id' => $createdBy->id,
            ]);
        });
    }
}
