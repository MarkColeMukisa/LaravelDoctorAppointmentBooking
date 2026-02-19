<?php

namespace Tests\Feature;

use App\Livewire\DoctorPatientRecords;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientStatusAudit;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DoctorPatientStatusApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_patients_page_contains_component(): void
    {
        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $this->actingAs($doctorUser)
            ->get(route('doctor-patients'))
            ->assertOk()
            ->assertSeeLivewire('doctor-patient-records');
    }

    public function test_assigned_doctor_can_update_patient_status_and_create_audit_entry(): void
    {
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

        Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '11:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($doctorUser);

        Livewire::test(DoctorPatientRecords::class)
            ->call('openStatusUpdateModal', $patient->id)
            ->set('pendingStatus', User::PATIENT_STATUS_ACTIVE)
            ->set('doctorApprovalNote', 'Approved after reviewing latest consultation outcomes and treatment compliance.')
            ->call('updatePatientStatus');

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'patient_status' => User::PATIENT_STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas((new PatientStatusAudit)->getTable(), [
            'patient_id' => $patient->id,
            'admin_id' => null,
            'doctor_id' => $doctor->id,
            'previous_status' => User::PATIENT_STATUS_INACTIVE,
            'new_status' => User::PATIENT_STATUS_ACTIVE,
            'doctor_approval_granted' => true,
        ]);
    }

    public function test_doctor_cannot_update_status_for_unassigned_patient(): void
    {
        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        $speciality = Specialities::query()->create(['speciality_name' => 'Neurology']);
        $doctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
        $doctor = Doctor::query()->create([
            'hospital_name' => 'Neuro Clinic',
            'speciality_id' => $speciality->id,
            'user_id' => $doctorUser->id,
            'bio' => 'Neurologist',
            'experience' => 9,
        ]);

        $otherDoctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
        $otherDoctor = Doctor::query()->create([
            'hospital_name' => 'General Clinic',
            'speciality_id' => $speciality->id,
            'user_id' => $otherDoctorUser->id,
            'bio' => 'General specialist',
            'experience' => 6,
        ]);

        Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $otherDoctor->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '15:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($doctorUser);

        Livewire::test(DoctorPatientRecords::class)
            ->call('openStatusUpdateModal', $patient->id)
            ->set('selectedPatientId', $patient->id)
            ->set('pendingStatus', User::PATIENT_STATUS_TRANSFERRED)
            ->set('doctorApprovalNote', 'Attempting to update a patient that is not assigned to this doctor.')
            ->call('updatePatientStatus')
            ->assertHasErrors(['selectedPatientId']);

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        $this->assertDatabaseCount((new PatientStatusAudit)->getTable(), 0);
    }
}
