<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEditDoctorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_edit_doctor_page_with_specialities(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $doctorUser = User::factory()->create([
            'name' => 'Sarah Mburu',
            'email' => 'doctor@example.com',
            'role' => User::ROLE_DOCTOR,
        ]);

        $cardiology = Specialities::query()->create([
            'speciality_name' => 'Cardiology',
        ]);

        $dermatology = Specialities::query()->create([
            'speciality_name' => 'Dermatology',
        ]);

        $doctor = Doctor::query()->create([
            'bio' => 'Consultant physician',
            'hospital_name' => 'Central Clinic',
            'speciality_id' => $cardiology->id,
            'user_id' => $doctorUser->id,
            'experience' => 10,
        ]);

        $response = $this->actingAs($admin)->get('/edit/doctor/'.$doctor->id);

        $response
            ->assertOk()
            ->assertSee('Edit Doctor')
            ->assertSee('Cardiology')
            ->assertSee('Dermatology')
            ->assertSee('doctor@example.com');
    }
}
