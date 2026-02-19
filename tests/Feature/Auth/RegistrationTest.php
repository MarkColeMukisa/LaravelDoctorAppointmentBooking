<?php

namespace Tests\Feature\Auth;

use App\Mail\DoctorApplicationReceived;
use App\Mail\DoctorApplicationSubmitted;
use App\Models\DoctorApplication;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register')
            ->assertSee('sm:max-w-3xl');
    }

    public function test_back_action_redirects_to_login_on_first_step(): void
    {
        Volt::test('pages.auth.register')
            ->call('back')
            ->assertRedirect(route('login', absolute: false));
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('contact_number', '+15550000000')
            ->set('address', '123 Main Street, Springfield')
            ->set('date_of_birth', '1992-05-10')
            ->set('gender', 'female');

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertSame('Account created successfully. Welcome to your patient dashboard.', session('message'));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => User::ROLE_PATIENT,
            'contact_number' => '+15550000000',
            'gender' => 'female',
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ]);
        $this->assertDatabaseCount('doctor_applications', 0);
    }

    public function test_doctor_registration_option_creates_doctor_application_and_sends_notifications(): void
    {
        Mail::fake();
        Storage::fake(config('filesystems.default'));

        config()->set('mail.admin.address', 'admin@clinic.test');

        $speciality = Specialities::query()->create([
            'speciality_name' => 'Cardiology',
        ]);

        $component = Volt::test('pages.auth.register')
            ->set('registrationType', 'doctor')
            ->set('name', 'Dr Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('doctor_name', 'Dr Jane Doe')
            ->set('doctor_email', 'dr.jane@example.com')
            ->set('doctor_hospital_name', 'City Clinic')
            ->set('doctor_speciality_id', (string) $speciality->id)
            ->set('doctor_experience', '9')
            ->set('doctor_bio', 'Cardiology specialist')
            ->set('doctor_profile_image', UploadedFile::fake()->image('doctor-profile.jpg'));

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $this->assertSame('Account created and doctor application submitted for admin review.', session('message'));

        $user = User::query()->where('email', 'jane@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->profile_image);
        Storage::disk(config('filesystems.default'))->assertExists($user->profile_image);
        $this->assertDatabaseHas('doctor_applications', [
            'user_id' => $user->id,
            'email' => 'dr.jane@example.com',
            'hospital_name' => 'City Clinic',
            'speciality_id' => $speciality->id,
            'status' => DoctorApplication::STATUS_PENDING,
        ]);

        Mail::assertSent(DoctorApplicationSubmitted::class, function (DoctorApplicationSubmitted $mail): bool {
            return $mail->hasTo('admin@clinic.test');
        });

        Mail::assertSent(DoctorApplicationReceived::class, function (DoctorApplicationReceived $mail): bool {
            return $mail->hasTo('dr.jane@example.com');
        });
    }

    public function test_doctor_registration_requires_profile_image(): void
    {
        $speciality = Specialities::query()->create([
            'speciality_name' => 'Cardiology',
        ]);

        Volt::test('pages.auth.register')
            ->set('registrationType', 'doctor')
            ->set('name', 'Dr Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('doctor_name', 'Dr Jane Doe')
            ->set('doctor_email', 'dr.jane@example.com')
            ->set('doctor_hospital_name', 'City Clinic')
            ->set('doctor_speciality_id', (string) $speciality->id)
            ->set('doctor_experience', '9')
            ->set('doctor_bio', 'Cardiology specialist')
            ->call('register')
            ->assertHasErrors(['doctor_profile_image' => 'required']);
    }
}
