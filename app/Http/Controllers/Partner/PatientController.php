<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Optical\StorePartnerPatientRequest;
use App\Http\Traits\ApiResponse;
use App\Models\OpticalPartner;
use App\Models\PatientProfile;
use App\Services\Optical\PatientRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PatientRegistrationService $registrationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var OpticalPartner $partner */
        $partner = $request->attributes->get('optical_partner');

        $patients = PatientProfile::with('profile.user')
            ->where('optical_partner_id', $partner->id)
            ->orderByDesc('id')
            ->paginate(20);

        return $this->jsonSuccess($patients, 'Pacientes del partner');
    }

    public function store(StorePartnerPatientRequest $request): JsonResponse
    {
        /** @var OpticalPartner $partner */
        $partner = $request->attributes->get('optical_partner');

        try {
            $patientProfile = $this->registrationService->registerUnderPartner(
                $partner,
                $request->validated(),
                $request->user()
            );
        } catch (\InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 409, 'PATIENT_EXISTS');
        }

        $patientProfile->load('profile.user');

        return $this->jsonSuccess($patientProfile, 'Paciente registrado', 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        /** @var OpticalPartner $partner */
        $partner = $request->attributes->get('optical_partner');

        $patientProfile = PatientProfile::with('profile.user')
            ->where('optical_partner_id', $partner->id)
            ->find($id);

        if (! $patientProfile) {
            return $this->jsonNotFound('Paciente no encontrado');
        }

        $this->authorize('view', $patientProfile);

        return $this->jsonSuccess($patientProfile);
    }
}
