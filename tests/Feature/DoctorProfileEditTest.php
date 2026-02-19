<?php

namespace Tests\Feature;

use App\Livewire\EditDoctor;
use App\Models\Doctor;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DoctorProfileEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_access_own_profile_edit_page(): void
    {
        $speciality = Specialities::query()->create([
            'speciality_name' => 'Cardiology',
        ]);

        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOCTOR,
        ]);

        Doctor::query()->create([
            'bio' => 'Consultant physician',
            'hospital_name' => 'Central Clinic',
            'speciality_id' => $speciality->id,
            'user_id' => $doctorUser->id,
            'experience' => 10,
        ]);

        $response = $this->actingAs($doctorUser)->get(route('doctor-profile-edit'));

        $response
            ->assertOk()
            ->assertSee('Edit My Doctor Profile')
            ->assertSeeLivewire('edit-doctor');
    }

    public function test_doctor_can_update_profile_details_and_image_from_edit_page(): void
    {
        Storage::fake(config('filesystems.default'));

        $speciality = Specialities::query()->create([
            'speciality_name' => 'Cardiology',
        ]);

        $doctorUser = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => User::ROLE_DOCTOR,
        ]);

        $doctor = Doctor::query()->create([
            'bio' => 'Consultant physician',
            'hospital_name' => 'Central Clinic',
            'speciality_id' => $speciality->id,
            'user_id' => $doctorUser->id,
            'experience' => 10,
        ]);

        $this->actingAs($doctorUser);

        Livewire::test(EditDoctor::class, ['doctor_id' => $doctor->id])
            ->set('name', 'Dr Updated')
            ->set('email', 'updated@example.com')
            ->set('bio', 'Updated bio')
            ->set('hospital_name', 'Updated Hospital')
            ->set('speciality_id', (string) $speciality->id)
            ->set('experience', '12')
            ->set('twitter', 'https://twitter.com/updated')
            ->set('instagram', 'https://instagram.com/updated')
            ->set('image', UploadedFile::fake()->image('updated-doctor.jpg'))
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect('/doctor/profile/edit');

        $doctor->refresh();
        $doctorUser->refresh();

        $this->assertSame('Updated bio', $doctor->bio);
        $this->assertSame('Updated Hospital', $doctor->hospital_name);
        $this->assertSame(12, $doctor->experience);
        $this->assertNotNull($doctor->image);
        Storage::disk(config('filesystems.default'))->assertExists($doctor->image);

        $this->assertSame('Updated', $doctorUser->name);
        $this->assertSame('updated@example.com', $doctorUser->email);
    }
}
