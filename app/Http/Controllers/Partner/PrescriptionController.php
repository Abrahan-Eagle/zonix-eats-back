<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Optical\OcrPrescriptionRequest;
use App\Http\Requests\Optical\StorePrescriptionRequest;
use App\Http\Traits\ApiResponse;
use App\Models\OpticalPartner;
use App\Models\PatientProfile;
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
        /** @var OpticalPartner $partner */
        $partner = $request->attributes->get('optical_partner');

        $query = Prescription::with('patientProfile.profile.user')
            ->where('optical_partner_id', $partner->id)
            ->orderByDesc('id');

        if ($request->filled('patient_profile_id')) {
            $query->where('patient_profile_id', (int) $request->query('patient_profile_id'));
        }

        return $this->jsonSuccess($query->paginate(20));
    }

    public function store(StorePrescriptionRequest $request): JsonResponse
    {
        /** @var OpticalPartner $partner */
        $partner = $request->attributes->get('optical_partner');

        $patientProfile = PatientProfile::where('optical_partner_id', $partner->id)
            ->find($request->integer('patient_profile_id'));

        if (! $patientProfile) {
            return $this->jsonNotFound('Paciente no encontrado en su óptica');
        }

        $this->authorize('create', Prescription::class);

        try {
            $prescription = $this->prescriptionService->createManual(
                $partner,
                $patientProfile,
                $request->validated(),
                $request->user()
            );
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422, 'PRESCRIPTION_INVALID');
        }

        return $this->jsonSuccess($prescription, 'Fórmula creada', 201);
    }

    public function ocr(OcrPrescriptionRequest $request): JsonResponse
    {
        if (! config('optical.ocr.enabled')) {
            return $this->jsonError('OCR deshabilitado', 503, 'OCR_DISABLED');
        }

        /** @var OpticalPartner $partner */
        $partner = $request->attributes->get('optical_partner');

        $patientProfile = PatientProfile::where('optical_partner_id', $partner->id)
            ->find($request->integer('patient_profile_id'));

        if (! $patientProfile) {
            return $this->jsonNotFound('Paciente no encontrado en su óptica');
        }

        try {
            $prescription = $this->prescriptionService->createFromOcr(
                $partner,
                $patientProfile,
                $request->validated(),
                $request->user()
            );
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422, 'OCR_FAILED');
        }

        return $this->jsonSuccess($prescription, 'Borrador OCR — requiere revisión', 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $prescription = Prescription::find($id);
        if (! $prescription) {
            return $this->jsonNotFound();
        }

        $this->authorize('view', $prescription);

        return $this->jsonSuccess($prescription);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $prescription = Prescription::find($id);
        if (! $prescription) {
            return $this->jsonNotFound();
        }

        $this->authorize('approve', $prescription);

        try {
            $prescription = $this->prescriptionService->approve($prescription, $request->user());
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422, 'APPROVE_INVALID');
        }

        return $this->jsonSuccess($prescription, 'Fórmula aprobada');
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $prescription = Prescription::find($id);
        if (! $prescription) {
            return $this->jsonNotFound();
        }

        $this->authorize('approve', $prescription);

        try {
            $prescription = $this->prescriptionService->reject(
                $prescription,
                $request->user(),
                $request->string('reason')->toString()
            );
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422, 'REJECT_INVALID');
        }

        return $this->jsonSuccess($prescription, 'Fórmula rechazada');
    }
}
