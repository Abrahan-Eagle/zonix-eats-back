<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Prescription;
use App\Services\Optical\PrescriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PrescriptionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PrescriptionService $prescriptionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $patientProfile = $request->user()->profile?->patientProfile;
        if (! $patientProfile) {
            return $this->jsonSuccess([]);
        }

        $prescriptions = Prescription::where('patient_profile_id', $patientProfile->id)
            ->orderByDesc('id')
            ->get();

        return $this->jsonSuccess($prescriptions);
    }

    public function confirm(Request $request, int $id): JsonResponse
    {
        $prescription = Prescription::find($id);
        if (! $prescription) {
            return $this->jsonNotFound();
        }

        $this->authorize('confirm', $prescription);

        $patientProfile = $request->user()->profile?->patientProfile;
        if (! $patientProfile) {
            return $this->jsonForbidden('Perfil de paciente no encontrado');
        }

        try {
            $prescription = $this->prescriptionService->patientConfirm($prescription, $patientProfile);
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422, 'CONFIRM_INVALID');
        }

        return $this->jsonSuccess($prescription, 'Fórmula confirmada por paciente');
    }
}
