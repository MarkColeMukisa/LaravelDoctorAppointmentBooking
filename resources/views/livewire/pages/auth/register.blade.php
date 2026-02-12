<?php

use App\Models\User;
use App\Models\DoctorApplication;
use App\Models\Specialities;
use App\Mail\DoctorApplicationSubmitted;
use App\Mail\DoctorApplicationReceived;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $doctor_name = '';
    public string $doctor_email = '';
    public string $doctor_hospital_name = '';
    public string $doctor_speciality_id = '';
    public string $doctor_experience = '';
    public string $doctor_bio = '';
    public bool $showDoctorModal = false;
    public $specialities = [];

    public function mount(): void
    {
        $this->specialities = Specialities::all();
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));
        $user->role = User::ROLE_PATIENT;
        $user->save();

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    public function openDoctorModal(): void
    {
        $this->doctor_name = $this->name;
        $this->doctor_email = $this->email;
        $this->showDoctorModal = true;
    }

    public function closeDoctorModal(): void
    {
        $this->showDoctorModal = false;
    }

    public function submitDoctorApplication(): void
    {
        $validated = $this->validate([
            'doctor_name' => ['required', 'string', 'max:255'],
            'doctor_email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'doctor_hospital_name' => ['required', 'string', 'max:255'],
            'doctor_speciality_id' => ['required', 'integer', 'exists:specialities,id'],
            'doctor_experience' => ['required', 'integer', 'min:0', 'max:80'],
            'doctor_bio' => ['required', 'string', 'max:2000'],
        ]);

        $application = DoctorApplication::create([
            'user_id' => Auth::id(),
            'name' => $validated['doctor_name'],
            'email' => $validated['doctor_email'],
            'hospital_name' => $validated['doctor_hospital_name'],
            'speciality_id' => $validated['doctor_speciality_id'],
            'experience' => $validated['doctor_experience'],
            'bio' => $validated['doctor_bio'],
            'status' => DoctorApplication::STATUS_PENDING,
        ]);

        $specialityName = $this->specialities
            ->firstWhere('id', (int) $validated['doctor_speciality_id'])
            ?->speciality_name ?? 'N/A';

        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');

        Mail::to($adminEmail)->send(new DoctorApplicationSubmitted([
            'name' => $application->name,
            'email' => $application->email,
            'hospital_name' => $application->hospital_name,
            'speciality_name' => $specialityName,
            'experience' => $application->experience,
            'bio' => $application->bio,
            'review_url' => url('/admin/doctors'),
        ]));

        Mail::to($application->email)->send(new DoctorApplicationReceived([
            'name' => $application->name,
            'speciality_name' => $specialityName,
        ]));

        $this->reset([
            'doctor_name',
            'doctor_email',
            'doctor_hospital_name',
            'doctor_speciality_id',
            'doctor_experience',
            'doctor_bio',
        ]);
        $this->showDoctorModal = false;

        session()->flash('message', 'Your doctor application has been submitted. You will receive an update after admin review.');
    }
}; ?>

<div>
    @if (session()->has('message'))
        <div wire:transition.opacity class="mb-4 bg-emerald-600 text-sm text-white rounded-lg p-3" role="alert">
            <span class="font-semibold">Success</span>
            <span class="ml-1">{{ session('message') }}.</span>
        </div>
    @endif
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 border-t border-gray-200 pt-4">
        <p class="text-sm text-gray-600">Registering creates a free patient account.</p>
        <button type="button" class="mt-2 text-sm font-semibold text-blue-600 hover:text-blue-700" wire:click="openDoctorModal">
            Apply to become a doctor
        </button>
    </div>

    @if ($showDoctorModal)
        <div wire:transition.opacity class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" wire:click="closeDoctorModal"></div>
            <div wire:transition.opacity class="relative w-full max-w-lg mx-4 rounded-2xl bg-white shadow-xl p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Doctor Application</h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700" wire:click="closeDoctorModal">x</button>
                </div>
                <p class="mt-2 text-sm text-gray-600">
                    Submit your details for admin review. You will be contacted after approval.
                </p>

                <div class="mt-4 space-y-3">
                    <div>
                        <x-input-label for="doctor_name" :value="__('Full Name')" />
                        <x-text-input wire:model="doctor_name" id="doctor_name" class="block mt-1 w-full" type="text" required />
                        <x-input-error :messages="$errors->get('doctor_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="doctor_email" :value="__('Email')" />
                        <x-text-input wire:model="doctor_email" id="doctor_email" class="block mt-1 w-full" type="email" required />
                        <x-input-error :messages="$errors->get('doctor_email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="doctor_hospital_name" :value="__('Hospital / Clinic')" />
                        <x-text-input wire:model="doctor_hospital_name" id="doctor_hospital_name" class="block mt-1 w-full" type="text" required />
                        <x-input-error :messages="$errors->get('doctor_hospital_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="doctor_speciality_id" :value="__('Speciality')" />
                        <select wire:model="doctor_speciality_id" id="doctor_speciality_id" class="block mt-1 w-full border-gray-300 rounded-md">
                            <option value="">Select speciality</option>
                            @foreach ($specialities as $speciality)
                                <option value="{{ $speciality->id }}">{{ $speciality->speciality_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('doctor_speciality_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="doctor_experience" :value="__('Years of Experience')" />
                        <x-text-input wire:model="doctor_experience" id="doctor_experience" class="block mt-1 w-full" type="number" min="0" max="80" required />
                        <x-input-error :messages="$errors->get('doctor_experience')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="doctor_bio" :value="__('Short Bio')" />
                        <textarea wire:model="doctor_bio" id="doctor_bio" rows="4" class="block mt-1 w-full border-gray-300 rounded-md"></textarea>
                        <x-input-error :messages="$errors->get('doctor_bio')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" class="text-sm text-gray-600 hover:text-gray-800" wire:click="closeDoctorModal">
                        Cancel
                    </button>
                    <x-primary-button wire:click="submitDoctorApplication">
                        Submit Application
                    </x-primary-button>
                </div>
            </div>
        </div>
    @endif
</div>
