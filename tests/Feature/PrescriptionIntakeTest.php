<?php

namespace Tests\Feature;

use App\Models\OpticalPartner;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionIntakeTest extends TestCase
{
    use RefreshDatabase;

    private function createPartnerUser(OpticalPartner $partner): User
    {
        $user = User::factory()->opticalPartner()->create();
        $user->opticalPartners()->attach($partner->id);

        return $user;
    }

    public function test_partner_can_register_patient_and_create_prescription(): void
    {
        $partner = OpticalPartner::factory()->create();
        $partnerUser = $this->createPartnerUser($partner);

        $register = $this->actingAs($partnerUser, 'sanctum')->postJson('/api/partner/patients', [
            'email' => 'paciente@test.com',
            'name' => 'Paciente Test',
            'firstName' => 'Paciente',
            'lastName' => 'Test',
        ]);
        $register->assertStatus(201);

        $patientProfileId = $register->json('data.id');

        $create = $this->actingAs($partnerUser, 'sanctum')->postJson('/api/partner/prescriptions', [
            'patient_profile_id' => $patientProfileId,
            'od_sphere' => -2.0,
            'oi_sphere' => -1.5,
            'pd' => 63,
        ]);
        $create->assertStatus(201)
            ->assertJsonPath('data.status', Prescription::STATUS_DRAFT);

        $prescriptionId = $create->json('data.id');

        $approve = $this->actingAs($partnerUser, 'sanctum')->postJson("/api/partner/prescriptions/{$prescriptionId}/approve");
        $approve->assertOk()
            ->assertJsonPath('data.status', Prescription::STATUS_APPROVED);
    }

    public function test_partner_cannot_approve_without_pd(): void
    {
        $partner = OpticalPartner::factory()->create();
        $partnerUser = $this->createPartnerUser($partner);
        $patientProfile = PatientProfile::factory()->create(['optical_partner_id' => $partner->id]);

        $prescription = Prescription::factory()->create([
            'patient_profile_id' => $patientProfile->id,
            'optical_partner_id' => $partner->id,
            'pd' => null,
            'status' => Prescription::STATUS_PENDING_REVIEW,
        ]);

        $response = $this->actingAs($partnerUser, 'sanctum')
            ->postJson("/api/partner/prescriptions/{$prescription->id}/approve");

        $response->assertStatus(422);
    }

    public function test_partner_cannot_access_other_tenant_prescription(): void
    {
        $partnerA = OpticalPartner::factory()->create();
        $partnerB = OpticalPartner::factory()->create();
        $userB = $this->createPartnerUser($partnerB);

        $patientA = PatientProfile::factory()->create(['optical_partner_id' => $partnerA->id]);
        $prescription = Prescription::factory()->create([
            'patient_profile_id' => $patientA->id,
            'optical_partner_id' => $partnerA->id,
        ]);

        $response = $this->actingAs($userB, 'sanctum')
            ->getJson("/api/partner/prescriptions/{$prescription->id}");

        $response->assertStatus(403);
    }

    public function test_patient_can_confirm_approved_prescription(): void
    {
        $partner = OpticalPartner::factory()->create();
        $user = User::factory()->user()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $patientProfile = PatientProfile::factory()->create([
            'profile_id' => $profile->id,
            'optical_partner_id' => $partner->id,
        ]);

        $prescription = Prescription::factory()->create([
            'patient_profile_id' => $patientProfile->id,
            'optical_partner_id' => $partner->id,
            'status' => Prescription::STATUS_APPROVED,
            'pd' => 62,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/patient/prescriptions/{$prescription->id}/confirm");

        $response->assertOk()
            ->assertJsonPath('data.status', Prescription::STATUS_PATIENT_CONFIRMED);
    }

    public function test_ocr_disabled_returns_503(): void
    {
        config(['optical.ocr.enabled' => false]);

        $partner = OpticalPartner::factory()->create();
        $partnerUser = $this->createPartnerUser($partner);
        $patientProfile = PatientProfile::factory()->create(['optical_partner_id' => $partner->id]);

        $response = $this->actingAs($partnerUser, 'sanctum')->postJson('/api/partner/prescriptions/ocr', [
            'patient_profile_id' => $patientProfile->id,
        ]);

        $response->assertStatus(503);
    }

    public function test_ocr_creates_pending_review_when_enabled(): void
    {
        config(['optical.ocr.enabled' => true]);

        $partner = OpticalPartner::factory()->create();
        $partnerUser = $this->createPartnerUser($partner);
        $patientProfile = PatientProfile::factory()->create(['optical_partner_id' => $partner->id]);

        $response = $this->actingAs($partnerUser, 'sanctum')->postJson('/api/partner/prescriptions/ocr', [
            'patient_profile_id' => $patientProfile->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', Prescription::STATUS_PENDING_REVIEW)
            ->assertJsonPath('data.source', Prescription::SOURCE_OCR_AI);
    }
}
