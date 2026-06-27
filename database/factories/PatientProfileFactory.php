<?php

namespace Database\Factories;

use App\Models\OpticalPartner;
use App\Models\PatientProfile;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientProfile>
 */
class PatientProfileFactory extends Factory
{
    protected $model = PatientProfile::class;

    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'optical_partner_id' => OpticalPartner::factory(),
            'clinical_notes' => null,
            'created_by_user_id' => null,
        ];
    }
}
