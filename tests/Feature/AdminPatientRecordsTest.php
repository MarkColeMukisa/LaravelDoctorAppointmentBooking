<?php

namespace Tests\Feature;

use App\Livewire\AdminPatientRecords;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientStatusChangeRequest;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPatientRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_keeps_summary_without_patient_records_component(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get(route('admin-dashboard'))
            ->assertOk()
            ->assertDontSeeLivewire('admin-patient-records');
    }

    public function test_admin_patients_page_contains_patient_records_component(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get(route('admin-patients'))
            ->assertOk()
            ->assertSeeLivewire('admin-patient-records');
    }

    public function test_admin_can_view_patient_records_data_with_appointment_summary(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'contact_number' => '+15552223333',
            'address' => '42 Cedar Street',
            'date_of_birth' => '1994-08-20',
            'gender' => 'female',
            'registration_date' => '2026-02-01',
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        $speciality = Specialities::query()->create([
            'speciality_name' => 'Family Medicine',
        ]);

        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $doctor = Doctor::query()->create([
            'hospital_name' => 'Community Hospital',
            'speciality_id' => $speciality->id,
            'user_id' => $doctorUser->id,
            'bio' => 'Family physician',
            'experience' => 12,
        ]);

        Appointment::query()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2026-02-14',
            'appointment_time' => '10:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($admin);

        Livewire::test(AdminPatientRecords::class)
            ->assertSee($patient->name)
            ->assertSee($patient->email)
            ->assertSee('+15552223333')
            ->assertSee('Total: 1')
            ->assertSee('Inactive');
    }

    public function test_admin_submits_pending_status_change_request_for_doctor_review(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $speciality = Specialities::query()->create([
            'speciality_name' => 'Internal Medicine',
        ]);

        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $doctor = Doctor::query()->create([
            'hospital_name' => 'Central Hospital',
            'speciality_id' => $speciality->id,
            'user_id' => $doctorUser->id,
            'bio' => 'Internist',
            'experience' => 9,
        ]);

        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        Appointment::query()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '11:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($admin);

        Livewire::test(AdminPatientRecords::class)
            ->call('openStatusApprovalModal', $patient->id)
            ->set('pendingStatus', User::PATIENT_STATUS_ACTIVE)
            ->set('selectedDoctorId', (string) $doctor->id)
            ->set('adminRequestNote', 'Clinical review requested for activation based on latest consultation outcomes.')
            ->call('submitStatusChangeRequest');

        $this->assertDatabaseHas('patient_status_change_requests', [
            'patient_id' => $patient->id,
            'admin_id' => $admin->id,
            'doctor_id' => $doctor->id,
            'current_status' => User::PATIENT_STATUS_INACTIVE,
            'requested_status' => User::PATIENT_STATUS_ACTIVE,
            'status' => PatientStatusChangeRequest::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);
    }

    public function test_admin_request_targets_latest_doctor_only_and_rejects_stale_doctor_submission(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $speciality = Specialities::query()->create([
            'speciality_name' => 'General Practice',
        ]);

        $olderDoctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $olderDoctor = Doctor::query()->create([
            'hospital_name' => 'North Clinic',
            'speciality_id' => $speciality->id,
            'user_id' => $olderDoctorUser->id,
            'bio' => 'Older assignment',
            'experience' => 6,
        ]);

        $latestDoctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $latestDoctor = Doctor::query()->create([
            'hospital_name' => 'South Clinic',
            'speciality_id' => $speciality->id,
            'user_id' => $latestDoctorUser->id,
            'bio' => 'Latest assignment',
            'experience' => 8,
        ]);

        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        Appointment::query()->create([
            'doctor_id' => $olderDoctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2026-02-10',
            'appointment_time' => '10:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_PENDING,
        ]);

        Appointment::query()->create([
            'doctor_id' => $latestDoctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2026-02-14',
            'appointment_time' => '11:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($admin);

        Livewire::test(AdminPatientRecords::class)
            ->call('openStatusApprovalModal', $patient->id)
            ->assertSet('selectedDoctorId', (string) $latestDoctor->id)
            ->set('pendingStatus', User::PATIENT_STATUS_ACTIVE)
            ->set('selectedDoctorId', (string) $olderDoctor->id)
            ->set('adminRequestNote', 'Attempting to submit with stale doctor assignment should be blocked by validation.')
            ->call('submitStatusChangeRequest')
            ->assertHasErrors(['selectedDoctorId']);

        $this->assertDatabaseCount('patient_status_change_requests', 0);
    }

    public function test_latest_doctor_assignment_ignores_cancelled_appointments(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $speciality = Specialities::query()->create([
            'speciality_name' => 'Family Medicine',
        ]);

        $activeDoctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $activeDoctor = Doctor::query()->create([
            'hospital_name' => 'Primary Clinic',
            'speciality_id' => $speciality->id,
            'user_id' => $activeDoctorUser->id,
            'bio' => 'Primary assignment',
            'experience' => 9,
        ]);

        $cancelledDoctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $cancelledDoctor = Doctor::query()->create([
            'hospital_name' => 'Backup Clinic',
            'speciality_id' => $speciality->id,
            'user_id' => $cancelledDoctorUser->id,
            'bio' => 'Cancelled latest appointment',
            'experience' => 7,
        ]);

        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        Appointment::query()->create([
            'doctor_id' => $activeDoctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2026-02-12',
            'appointment_time' => '09:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_PENDING,
        ]);

        Appointment::query()->create([
            'doctor_id' => $cancelledDoctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => '2026-02-14',
            'appointment_time' => '12:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        $this->actingAs($admin);

        Livewire::test(AdminPatientRecords::class)
            ->call('openStatusApprovalModal', $patient->id)
            ->assertSet('selectedDoctorId', (string) $activeDoctor->id)
            ->set('pendingStatus', User::PATIENT_STATUS_ACTIVE)
            ->set('adminRequestNote', 'Use the latest non-cancelled doctor assignment for this status change.')
            ->call('submitStatusChangeRequest');

        $this->assertDatabaseHas('patient_status_change_requests', [
            'patient_id' => $patient->id,
            'doctor_id' => $activeDoctor->id,
            'status' => PatientStatusChangeRequest::STATUS_PENDING,
        ]);
    }

    public function test_admin_cannot_submit_duplicate_pending_request_for_same_patient(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $speciality = Specialities::query()->create([
            'speciality_name' => 'Neurology',
        ]);

        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $doctor = Doctor::query()->create([
            'hospital_name' => 'Neuro Clinic',
            'speciality_id' => $speciality->id,
            'user_id' => $doctorUser->id,
            'bio' => 'Neurologist',
            'experience' => 7,
        ]);

        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        Appointment::query()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '13:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_PENDING,
        ]);

        PatientStatusChangeRequest::query()->create([
            'patient_id' => $patient->id,
            'admin_id' => $admin->id,
            'doctor_id' => $doctor->id,
            'current_status' => User::PATIENT_STATUS_INACTIVE,
            'requested_status' => User::PATIENT_STATUS_ACTIVE,
            'status' => PatientStatusChangeRequest::STATUS_PENDING,
            'admin_request_note' => 'Existing pending request.',
        ]);

        $this->actingAs($admin);

        Livewire::test(AdminPatientRecords::class)
            ->call('openStatusApprovalModal', $patient->id)
            ->set('pendingStatus', User::PATIENT_STATUS_TRANSFERRED)
            ->set('selectedDoctorId', (string) $doctor->id)
            ->set('adminRequestNote', 'Second request should fail while first one is pending.')
            ->call('submitStatusChangeRequest')
            ->assertHasErrors(['pendingStatus']);

        $this->assertDatabaseCount('patient_status_change_requests', 1);
    }

    public function test_non_admin_cannot_submit_status_change_request(): void
    {
        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        $this->actingAs($doctorUser);

        Livewire::test(AdminPatientRecords::class)
            ->call('openStatusApprovalModal', $patient->id)
            ->set('pendingStatus', User::PATIENT_STATUS_ACTIVE)
            ->set('adminRequestNote', 'Not allowed')
            ->call('submitStatusChangeRequest');

        $this->assertDatabaseCount('patient_status_change_requests', 0);
    }

    public function test_admin_can_open_print_audit_report_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $speciality = Specialities::query()->create([
            'speciality_name' => 'Emergency Medicine',
        ]);

        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $doctor = Doctor::query()->create([
            'hospital_name' => 'Metro Hospital',
            'speciality_id' => $speciality->id,
            'user_id' => $doctorUser->id,
            'bio' => 'ER physician',
            'experience' => 10,
        ]);

        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        PatientStatusChangeRequest::query()->create([
            'patient_id' => $patient->id,
            'admin_id' => $admin->id,
            'doctor_id' => $doctor->id,
            'current_status' => User::PATIENT_STATUS_INACTIVE,
            'requested_status' => User::PATIENT_STATUS_TRANSFERRED,
            'status' => PatientStatusChangeRequest::STATUS_REJECTED,
            'admin_request_note' => 'Transfer request submitted for review.',
            'doctor_decision_note' => 'Rejected because clinical handover is not complete.',
            'decided_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin-patient-audits-print'))
            ->assertOk()
            ->assertSee('Patient Status Audit Report')
            ->assertSee('Rejected')
            ->assertSee($patient->name);
    }

    public function test_admin_can_export_audit_csv(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        Livewire::test(AdminPatientRecords::class)
            ->call('exportAuditCsv')
            ->assertFileDownloaded();
    }
}
