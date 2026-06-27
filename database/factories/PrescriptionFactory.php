<?php

namespace Database\Factories;

use App\Models\OpticalPartner;
use App\Models\PatientProfile;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prescription>
 */
class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        return [
            'patient_profile_id' => PatientProfile::factory(),
            'optical_partner_id' => OpticalPartner::factory(),
            'od_sphere' => -2.00,
            'od_cylinder' => -0.50,
            'od_axis' => 90,
            'oi_sphere' => -1.75,
            'oi_cylinder' => -0.25,
            'oi_axis' => 85,
            'addition' => null,
            'pd' => 63.0,
            'pd_near' => null,
            'source' => Prescription::SOURCE_MANUAL,
            'status' => Prescription::STATUS_DRAFT,
            'document_id' => null,
        ];
    }
}
