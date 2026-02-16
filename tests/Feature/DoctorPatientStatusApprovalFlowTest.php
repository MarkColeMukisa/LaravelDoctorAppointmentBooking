<?php

namespace Tests\Feature;

use App\Livewire\DoctorPatientStatusRequests;
use App\Models\Doctor;
use App\Models\PatientStatusChangeRequest;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DoctorPatientStatusApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_status_requests_page_contains_component(): void
    {
        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $this->actingAs($doctorUser)
            ->get(route('doctor-patient-status-requests'))
            ->assertOk()
            ->assertSeeLivewire('doctor-patient-status-requests');
    }

    public function test_assigned_doctor_can_approve_request_and_apply_patient_status(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        $speciality = Specialities::query()->create(['speciality_name' => 'Cardiology']);

        $doctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
        $doctor = Doctor::query()->create([
            'hospital_name' => 'City Cardiac Centre',
            'speciality_id' => $speciality->id,
            'user_id' => $doctorUser->id,
            'bio' => 'Cardiologist',
            'experience' => 11,
        ]);

        $request = PatientStatusChangeRequest::query()->create([
            'patient_id' => $patient->id,
            'admin_id' => $admin->id,
            'doctor_id' => $doctor->id,
            'current_status' => User::PATIENT_STATUS_INACTIVE,
            'requested_status' => User::PATIENT_STATUS_ACTIVE,
            'status' => PatientStatusChangeRequest::STATUS_PENDING,
            'admin_request_note' => 'Please review and approve patient activation after current treatment plan.',
        ]);

        $this->actingAs($doctorUser);

        Livewire::test(DoctorPatientStatusRequests::class)
            ->call('openDecisionModal', $request->id, PatientStatusChangeRequest::STATUS_APPROVED)
            ->set('doctorDecisionNote', 'Approved after reviewing latest consultation outcomes and treatment compliance.')
            ->call('confirmDecision');

        $this->assertDatabaseHas('patient_status_change_requests', [
            'id' => $request->id,
            'status' => PatientStatusChangeRequest::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'patient_status' => User::PATIENT_STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('patient_status_audits', [
            'patient_id' => $patient->id,
            'admin_id' => $admin->id,
            'doctor_id' => $doctor->id,
            'previous_status' => User::PATIENT_STATUS_INACTIVE,
            'new_status' => User::PATIENT_STATUS_ACTIVE,
            'doctor_approval_granted' => true,
        ]);
    }

    public function test_assigned_doctor_can_reject_request_without_changing_patient_status(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        $speciality = Specialities::query()->create(['speciality_name' => 'Dermatology']);

        $doctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
        $doctor = Doctor::query()->create([
            'hospital_name' => 'Skin Clinic',
            'speciality_id' => $speciality->id,
            'user_id' => $doctorUser->id,
            'bio' => 'Dermatologist',
            'experience' => 8,
        ]);

        $request = PatientStatusChangeRequest::query()->create([
            'patient_id' => $patient->id,
            'admin_id' => $admin->id,
            'doctor_id' => $doctor->id,
            'current_status' => User::PATIENT_STATUS_INACTIVE,
            'requested_status' => User::PATIENT_STATUS_TRANSFERRED,
            'status' => PatientStatusChangeRequest::STATUS_PENDING,
            'admin_request_note' => 'Transfer request requires doctor review and recommendation.',
        ]);

        $this->actingAs($doctorUser);

        Livewire::test(DoctorPatientStatusRequests::class)
            ->call('openDecisionModal', $request->id, PatientStatusChangeRequest::STATUS_REJECTED)
            ->set('doctorDecisionNote', 'Rejected because patient still requires active follow-up under current care team.')
            ->call('confirmDecision');

        $this->assertDatabaseHas('patient_status_change_requests', [
            'id' => $request->id,
            'status' => PatientStatusChangeRequest::STATUS_REJECTED,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        $this->assertDatabaseCount('patient_status_audits', 0);
    }

    public function test_other_doctor_cannot_decide_request_not_assigned_to_them(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        $speciality = Specialities::query()->create(['speciality_name' => 'Orthopedics']);

        $assignedDoctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
        $assignedDoctor = Doctor::query()->create([
            'hospital_name' => 'Ortho Center',
            'speciality_id' => $speciality->id,
            'user_id' => $assignedDoctorUser->id,
            'bio' => 'Orthopedic specialist',
            'experience' => 10,
        ]);

        $otherDoctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
        Doctor::query()->create([
            'hospital_name' => 'General Clinic',
            'speciality_id' => $speciality->id,
            'user_id' => $otherDoctorUser->id,
            'bio' => 'General specialist',
            'experience' => 6,
        ]);

        $request = PatientStatusChangeRequest::query()->create([
            'patient_id' => $patient->id,
            'admin_id' => $admin->id,
            'doctor_id' => $assignedDoctor->id,
            'current_status' => User::PATIENT_STATUS_INACTIVE,
            'requested_status' => User::PATIENT_STATUS_ACTIVE,
            'status' => PatientStatusChangeRequest::STATUS_PENDING,
            'admin_request_note' => 'Only assigned doctor can decide this request.',
        ]);

        $this->actingAs($otherDoctorUser);

        Livewire::test(DoctorPatientStatusRequests::class)
            ->call('openDecisionModal', $request->id, PatientStatusChangeRequest::STATUS_APPROVED)
            ->set('doctorDecisionNote', 'Attempt by non-assigned doctor should not be accepted.')
            ->call('confirmDecision');

        $this->assertDatabaseHas('patient_status_change_requests', [
            'id' => $request->id,
            'status' => PatientStatusChangeRequest::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        $this->assertDatabaseCount('patient_status_audits', 0);
    }
}
