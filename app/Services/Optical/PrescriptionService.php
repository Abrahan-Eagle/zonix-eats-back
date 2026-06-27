<?php

namespace App\Services\Optical;

use App\Contracts\PrescriptionOcrServiceInterface;
use App\Models\OpticalPartner;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PrescriptionService
{
    public function __construct(
        private readonly PrescriptionOcrServiceInterface $ocrService
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createManual(
        OpticalPartner $partner,
        PatientProfile $patientProfile,
        array $data,
        User $actor,
        string $source = Prescription::SOURCE_MANUAL
    ): Prescription {
        $this->assertPatientBelongsToPartner($patientProfile, $partner);

        $status = $source === Prescription::SOURCE_OCR_AI
            ? Prescription::STATUS_PENDING_REVIEW
            : Prescription::STATUS_DRAFT;

        return Prescription::create([
            ...$this->onlyPrescriptionFields($data),
            'patient_profile_id' => $patientProfile->id,
            'optical_partner_id' => $partner->id,
            'source' => $source,
            'status' => $status,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function createFromOcr(
        OpticalPartner $partner,
        PatientProfile $patientProfile,
        array $context,
        User $actor
    ): Prescription {
        if (! config('optical.ocr.enabled')) {
            throw new InvalidArgumentException('OCR deshabilitado');
        }

        $draft = $this->ocrService->extractDraft($context);

        return $this->createManual(
            $partner,
            $patientProfile,
            $draft,
            $actor,
            Prescription::SOURCE_OCR_AI
        );
    }

    public function approve(Prescription $prescription, User $approver): Prescription
    {
        if (! in_array($prescription->status, [
            Prescription::STATUS_DRAFT,
            Prescription::STATUS_PENDING_REVIEW,
        ], true)) {
            throw new InvalidArgumentException('Estado no permite aprobación');
        }

        if ($prescription->pd === null) {
            throw new InvalidArgumentException('PD obligatoria para aprobar');
        }

        $prescription->update([
            'status' => Prescription::STATUS_APPROVED,
            'approved_by_user_id' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return $prescription->fresh();
    }

    public function reject(Prescription $prescription, User $approver, string $reason): Prescription
    {
        if (! in_array($prescription->status, [
            Prescription::STATUS_DRAFT,
            Prescription::STATUS_PENDING_REVIEW,
        ], true)) {
            throw new InvalidArgumentException('Estado no permite rechazo');
        }

        $prescription->update([
            'status' => Prescription::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'approved_by_user_id' => $approver->id,
            'approved_at' => now(),
        ]);

        return $prescription->fresh();
    }

    public function patientConfirm(Prescription $prescription, PatientProfile $patientProfile): Prescription
    {
        if ((int) $prescription->patient_profile_id !== (int) $patientProfile->id) {
            throw new InvalidArgumentException('Fórmula no pertenece al paciente');
        }

        if ($prescription->status !== Prescription::STATUS_APPROVED) {
            throw new InvalidArgumentException('Fórmula debe estar approved');
        }

        $prescription->update([
            'status' => Prescription::STATUS_PATIENT_CONFIRMED,
            'patient_confirmed_at' => now(),
        ]);

        return $prescription->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyPrescriptionFields(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'od_sphere', 'od_cylinder', 'od_axis',
            'oi_sphere', 'oi_cylinder', 'oi_axis',
            'addition', 'pd', 'pd_near', 'document_id',
        ]));
    }

    private function assertPatientBelongsToPartner(PatientProfile $patientProfile, OpticalPartner $partner): void
    {
        if ((int) $patientProfile->optical_partner_id !== (int) $partner->id) {
            throw new InvalidArgumentException('Paciente no pertenece al partner');
        }
    }
}
