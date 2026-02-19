<?php

namespace Tests\Feature;

use App\Livewire\DoctorPatientRecords;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPatientRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_navigation_does_not_expose_patient_records_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get(route('admin-dashboard'))
            ->assertOk()
            ->assertDontSee('href="/admin/patients"', false)
            ->assertDontSee('admin-patient-records');
    }

    public function test_admin_patients_routes_are_not_available(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get('/admin/patients')
            ->assertNotFound();

        $this->actingAs($admin)
            ->get('/admin/patients/audits/print')
            ->assertNotFound();
    }

    public function test_doctor_patients_page_contains_patient_records_component(): void
    {
        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $this->actingAs($doctorUser)
            ->get(route('doctor-patients'))
            ->assertOk()
            ->assertSeeLivewire('doctor-patient-records');
    }

    public function test_doctor_can_only_see_patients_from_their_appointments(): void
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

        $otherDoctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        $otherDoctor = Doctor::query()->create([
            'hospital_name' => 'County Hospital',
            'speciality_id' => $speciality->id,
            'user_id' => $otherDoctorUser->id,
            'bio' => 'Consultant physician',
            'experience' => 8,
        ]);

        $assignedPatient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
        ]);

        $unassignedPatient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
        ]);

        Appointment::query()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $assignedPatient->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '09:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_PENDING,
        ]);

        Appointment::query()->create([
            'doctor_id' => $otherDoctor->id,
            'patient_id' => $unassignedPatient->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '11:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($doctorUser);

        Livewire::test(DoctorPatientRecords::class)
            ->assertSee($assignedPatient->name)
            ->assertDontSee($unassignedPatient->name);
    }

    public function test_doctor_patient_records_show_age_computed_from_date_of_birth(): void
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

        $dateOfBirth = Carbon::today()->subYears(34)->toDateString();

        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'date_of_birth' => $dateOfBirth,
            'contact_number' => '08000000000',
        ]);

        Appointment::query()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '09:00:00',
            'appointment_type' => 0,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($doctorUser);

        Livewire::test(DoctorPatientRecords::class)
            ->assertSee('Age')
            ->assertSee(Carbon::parse($dateOfBirth)->age.' yrs');
    }
}
