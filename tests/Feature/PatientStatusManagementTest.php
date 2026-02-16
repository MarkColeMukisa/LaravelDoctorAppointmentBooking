<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientStatusManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_appointments_page_does_not_expose_direct_patient_status_updates(): void
    {
        $speciality = Specialities::query()->create([
            'speciality_name' => 'General Medicine',
        ]);

        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $doctor = Doctor::query()->create([
            'hospital_name' => 'City Hospital',
            'speciality_id' => $speciality->id,
            'user_id' => $doctorUser->id,
            'bio' => 'General physician',
            'experience' => 10,
        ]);

        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

        Appointment::query()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '09:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($doctorUser)
            ->get(route('doctor-appointments'))
            ->assertOk()
            ->assertDontSee('updatePatientStatus');
    }

    public function test_doctor_has_dedicated_status_requests_page(): void
    {
        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $this->actingAs($doctorUser)
            ->get(route('doctor-patient-status-requests'))
            ->assertOk()
            ->assertSee('Patient Status Requests');
    }
}
