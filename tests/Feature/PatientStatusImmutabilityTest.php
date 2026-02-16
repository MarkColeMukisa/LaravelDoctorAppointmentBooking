<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PatientStatusImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_status_audit_rows_cannot_be_updated_at_database_level(): void
    {
        [$admin, $patient, $doctor] = $this->seedActors();
        $now = now();

        $auditId = DB::table('patient_status_audits')->insertGetId([
            'patient_id' => $patient->id,
            'admin_id' => $admin->id,
            'doctor_id' => $doctor->id,
            'previous_status' => User::PATIENT_STATUS_INACTIVE,
            'new_status' => User::PATIENT_STATUS_ACTIVE,
            'doctor_approval_note' => 'Approved after review.',
            'doctor_approval_granted' => true,
            'approved_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->expectException(QueryException::class);

        DB::table('patient_status_audits')
            ->where('id', $auditId)
            ->update([
                'new_status' => User::PATIENT_STATUS_TRANSFERRED,
                'updated_at' => now(),
            ]);
    }

    public function test_patient_status_audit_rows_cannot_be_deleted_at_database_level(): void
    {
        [$admin, $patient, $doctor] = $this->seedActors();
        $now = now();

        $auditId = DB::table('patient_status_audits')->insertGetId([
            'patient_id' => $patient->id,
            'admin_id' => $admin->id,
            'doctor_id' => $doctor->id,
            'previous_status' => User::PATIENT_STATUS_INACTIVE,
            'new_status' => User::PATIENT_STATUS_ACTIVE,
            'doctor_approval_note' => 'Approved after review.',
            'doctor_approval_granted' => true,
            'approved_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->expectException(QueryException::class);

        DB::table('patient_status_audits')->where('id', $auditId)->delete();
    }

    public function test_finalized_status_change_request_cannot_be_updated_again_at_database_level(): void
    {
        [$admin, $patient, $doctor] = $this->seedActors();
        $now = now();

        $requestId = DB::table('patient_status_change_requests')->insertGetId([
            'patient_id' => $patient->id,
            'admin_id' => $admin->id,
            'doctor_id' => $doctor->id,
            'current_status' => User::PATIENT_STATUS_INACTIVE,
            'requested_status' => User::PATIENT_STATUS_ACTIVE,
            'status' => 'pending',
            'admin_request_note' => 'Please review and approve activation.',
            'doctor_decision_note' => null,
            'decided_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('patient_status_change_requests')
            ->where('id', $requestId)
            ->update([
                'status' => 'approved',
                'doctor_decision_note' => 'Approved by doctor.',
                'decided_at' => now(),
                'updated_at' => now(),
            ]);

        $this->expectException(QueryException::class);

        DB::table('patient_status_change_requests')
            ->where('id', $requestId)
            ->update([
                'doctor_decision_note' => 'Trying to modify finalized decision.',
                'updated_at' => now(),
            ]);
    }

    public function test_status_change_request_rows_cannot_be_deleted_at_database_level(): void
    {
        [$admin, $patient, $doctor] = $this->seedActors();
        $now = now();

        $requestId = DB::table('patient_status_change_requests')->insertGetId([
            'patient_id' => $patient->id,
            'admin_id' => $admin->id,
            'doctor_id' => $doctor->id,
            'current_status' => User::PATIENT_STATUS_INACTIVE,
            'requested_status' => User::PATIENT_STATUS_ACTIVE,
            'status' => 'pending',
            'admin_request_note' => 'Please review and approve activation.',
            'doctor_decision_note' => null,
            'decided_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->expectException(QueryException::class);

        DB::table('patient_status_change_requests')->where('id', $requestId)->delete();
    }

    private function seedActors(): array
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $patient = User::factory()->create([
            'role' => User::ROLE_PATIENT,
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);

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
            'experience' => 9,
        ]);

        return [$admin, $patient, $doctor];
    }
}
