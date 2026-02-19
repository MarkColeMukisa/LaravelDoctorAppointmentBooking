<?php

use App\Mail\DoctorApplicationReceived;
use App\Mail\DoctorApplicationSubmitted;
use App\Models\DoctorApplication;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.guest')] class extends Component
{
    use WithFileUploads;

    public string $registrationType = 'patient';

    public int $currentStep = 1;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $contact_number = '';

    public string $address = '';

    public string $date_of_birth = '';

    public string $gender = '';

    public string $doctor_name = '';

    public string $doctor_email = '';

    public string $doctor_hospital_name = '';

    public string $doctor_speciality_id = '';

    public string $doctor_experience = '';

    public string $doctor_bio = '';

    public $doctor_profile_image;

    public $specialities = [];

    public array $genders = [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
        'prefer_not_to_say' => 'Prefer not to say',
    ];

    public function mount(): void
    {
        $this->specialities = [];
    }

    public function selectRegistrationType(string $type): void
    {
        if (! in_array($type, ['patient', 'doctor'], true)) {
            return;
        }

        $this->registrationType = $type;

        if ($type === 'doctor') {
            $this->loadSpecialities();
            $this->doctor_name = $this->doctor_name !== '' ? $this->doctor_name : $this->name;
            $this->doctor_email = $this->doctor_email !== '' ? $this->doctor_email : $this->email;
            return;
        }

        $this->resetValidation([
            'doctor_name',
            'doctor_email',
            'doctor_hospital_name',
            'doctor_speciality_id',
            'doctor_experience',
            'doctor_bio',
            'doctor_profile_image',
        ]);
    }

    public function nextStep(): void
    {
        $this->validate([
            'registrationType' => ['required', 'in:patient,doctor'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($this->registrationType === 'doctor') {
            $this->loadSpecialities();
            $this->doctor_name = $this->doctor_name !== '' ? $this->doctor_name : $this->name;
            $this->doctor_email = $this->doctor_email !== '' ? $this->doctor_email : $this->email;
        }

        $this->currentStep = 2;
    }

    public function previousStep(): void
    {
        $this->currentStep = 1;
    }

    public function back(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            return;
        }

        $this->redirect(route('login', absolute: false), navigate: true);
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        if ($this->registrationType === 'doctor') {
            $this->loadSpecialities();
            if ($this->doctor_name === '') {
                $this->doctor_name = $this->name;
            }

            if ($this->doctor_email === '') {
                $this->doctor_email = $this->email;
            }
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];

        if ($this->registrationType === 'patient') {
            $rules = array_merge($rules, [
                'contact_number' => ['required', 'string', 'min:7', 'max:20'],
                'address' => ['required', 'string', 'max:500'],
                'date_of_birth' => ['required', 'date', 'before:today'],
                'gender' => ['required', 'in:male,female,other,prefer_not_to_say'],
            ]);
        }

        if ($this->registrationType === 'doctor') {
            $rules = array_merge($rules, [
                'doctor_name' => ['required', 'string', 'max:255'],
                'doctor_email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
                'doctor_hospital_name' => ['required', 'string', 'max:255'],
                'doctor_speciality_id' => ['required', 'integer', 'exists:specialities,id'],
                'doctor_experience' => ['required', 'integer', 'min:0', 'max:80'],
                'doctor_bio' => ['required', 'string', 'max:2000'],
                'doctor_profile_image' => ['required', 'image', 'max:2048'],
            ]);
        }

        $validated = $this->validate($rules, [
            'doctor_speciality_id.required' => 'Please select your speciality.',
            'doctor_hospital_name.required' => 'Please provide your hospital or clinic name.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
        ]);

        $successMessage = $this->registrationType === 'doctor'
            ? 'Account created and doctor application submitted for admin review.'
            : 'Account created successfully. Welcome to your patient dashboard.';

        $validated['password'] = Hash::make($validated['password']);

        $userPayload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'patient_status' => User::PATIENT_STATUS_INACTIVE,
        ];

        if ($this->registrationType === 'patient') {
            $userPayload = array_merge($userPayload, [
                'contact_number' => $validated['contact_number'],
                'address' => $validated['address'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'registration_date' => now()->toDateString(),
            ]);
        }

        event(new Registered($user = User::create([
            ...$userPayload,
        ])));

        $user->role = User::ROLE_PATIENT;
        if ($this->registrationType === 'doctor') {
            $user->profile_image = $this->doctor_profile_image->store('public/images');
        }
        $user->save();

        if ($this->registrationType === 'doctor') {
            $application = DoctorApplication::query()->create([
                'user_id' => $user->id,
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

            Mail::to(config('mail.admin.address'))->send(new DoctorApplicationSubmitted([
                'name' => $application->name,
                'email' => $application->email,
                'hospital_name' => $application->hospital_name,
                'speciality_name' => $specialityName,
                'experience' => $application->experience,
                'bio' => $application->bio,
                'review_url' => route('admin-doctor-applications', absolute: true),
            ]));

            Mail::to($application->email)->send(new DoctorApplicationReceived([
                'name' => $application->name,
                'speciality_name' => $specialityName,
            ]));
        }

        Auth::login($user);
        session()->flash('message', $successMessage);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    private function loadSpecialities(): void
    {
        if (count($this->specialities) > 0) {
            return;
        }

        $this->specialities = Specialities::query()
            ->select(['id', 'speciality_name'])
            ->orderBy('speciality_name')
            ->get();
    }
}; ?>


<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Registration Steps</span>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $currentStep === 1 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700' }}">
                Step 1: Account
            </span>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $currentStep === 2 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700' }}">
                Step 2: {{ $registrationType === 'doctor' ? 'Doctor Details' : 'Patient Details' }}
            </span>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        @if ($currentStep === 1)
        <div wire:transition class="space-y-4">
            <p class="text-sm font-semibold text-slate-900">Step 1: Choose Account Type and Create Credentials</p>

            <div class="inline-flex rounded-full bg-slate-100 p-1">
                <button
                    type="button"
                    wire:click="selectRegistrationType('patient')"
                    class="rounded-full px-4 py-2 text-sm font-semibold transition"
                    @class([ 'bg-white text-slate-900 shadow-sm'=> $registrationType === 'patient',
                    'text-slate-600 hover:text-slate-900' => $registrationType !== 'patient',
                    ])
                    >
                    Patient
                </button>
                <button
                    type="button"
                    wire:click="selectRegistrationType('doctor')"
                    class="rounded-full px-4 py-2 text-sm font-semibold transition"
                    @class([ 'bg-white text-slate-900 shadow-sm'=> $registrationType === 'doctor',
                    'text-slate-600 hover:text-slate-900' => $registrationType !== 'doctor',
                    ])
                    >
                    Doctor
                </button>
            </div>

            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input wire:model="name" id="name" class="mt-1 block w-full" type="text" name="name" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input wire:model="email" id="email" class="mt-1 block w-full" type="email" name="email" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input wire:model="password" id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <x-primary-button type="button" wire:click="back">
                    Back
                </x-primary-button>
                <a class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" href="{{ route('login') }}" wire:navigate>
                    {{ __('Already registered?') }}
                </a>
                <x-primary-button type="button" wire:click="nextStep">
                    Continue
                </x-primary-button>
            </div>
        </div>
        @endif

        @if ($currentStep === 2)
        <div wire:transition class="space-y-4">
            <p class="text-sm font-semibold text-slate-900">
                Step 2: {{ $registrationType === 'doctor' ? 'Doctor Application Details' : 'Patient Details' }}
            </p>

            @if ($registrationType === 'patient')
            <div class="space-y-3 rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 transition-all duration-300">
                <p class="text-sm font-semibold text-emerald-900">Patient Registration Details</p>

                <div>
                    <x-input-label for="contact_number" :value="__('Contact Number')" />
                    <x-text-input wire:model="contact_number" id="contact_number" class="mt-1 block w-full" type="text" placeholder="+1 555 000 0000" />
                    <x-input-error class="mt-2" :messages="$errors->get('contact_number')" />
                </div>

                <div>
                    <x-input-label for="address" :value="__('Address')" />
                    <textarea wire:model="address" id="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('address')" />
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                        <x-text-input wire:model="date_of_birth" id="date_of_birth" class="mt-1 block w-full" type="date" />
                        <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
                    </div>

                    <div>
                        <x-input-label for="gender" :value="__('Gender')" />
                        <select wire:model="gender" id="gender" class="mt-1 block w-full rounded-md border-gray-300">
                            <option value="">Select gender</option>
                            @foreach ($genders as $genderValue => $genderLabel)
                            <option value="{{ $genderValue }}">{{ $genderLabel }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('gender')" />
                    </div>
                </div>

                <div class="rounded-xl border border-emerald-200 bg-white p-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-800">System Fields</p>
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-700">
                        <span>Registration date: <span class="font-semibold">{{ now()->toDateString() }}</span></span>
                        <span class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">
                            Inactive
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-slate-600">
                        Patient accounts start as inactive and can be updated by a doctor.
                    </p>
                </div>
            </div>
            @endif

            @if ($registrationType === 'doctor')
            <div class="space-y-3 rounded-2xl border border-blue-200 bg-blue-50/70 p-4 transition-all duration-300">
                <p class="text-sm font-semibold text-blue-900">Doctor Application Details</p>

                <div>
                    <x-input-label for="doctor_name" :value="__('Professional Name')" />
                    <x-text-input wire:model="doctor_name" id="doctor_name" class="mt-1 block w-full" type="text" />
                    <x-input-error class="mt-2" :messages="$errors->get('doctor_name')" />
                </div>

                <div>
                    <x-input-label for="doctor_email" :value="__('Professional Email')" />
                    <x-text-input wire:model="doctor_email" id="doctor_email" class="mt-1 block w-full" type="email" />
                    <x-input-error class="mt-2" :messages="$errors->get('doctor_email')" />
                </div>

                <div>
                    <x-input-label for="doctor_profile_image" :value="__('Profile Image')" />
                    <input wire:model="doctor_profile_image" id="doctor_profile_image" type="file" accept="image/*" class="mt-1 block w-full rounded-md border-gray-300" />
                    @if ($doctor_profile_image)
                        <img src="{{ $doctor_profile_image->temporaryUrl() }}" class="mt-2 h-24 w-24 rounded-lg object-cover" alt="Doctor profile image preview">
                    @endif
                    <x-input-error class="mt-2" :messages="$errors->get('doctor_profile_image')" />
                </div>

                <div>
                    <x-input-label for="doctor_hospital_name" :value="__('Hospital / Clinic')" />
                    <x-text-input wire:model="doctor_hospital_name" id="doctor_hospital_name" class="mt-1 block w-full" type="text" />
                    <x-input-error class="mt-2" :messages="$errors->get('doctor_hospital_name')" />
                </div>

                <div>
                    <x-input-label for="doctor_speciality_id" :value="__('Speciality')" />
                    <select wire:model="doctor_speciality_id" id="doctor_speciality_id" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">Select speciality</option>
                        @foreach ($specialities as $speciality)
                        <option value="{{ $speciality->id }}">{{ $speciality->speciality_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('doctor_speciality_id')" />
                </div>

                <div>
                    <x-input-label for="doctor_experience" :value="__('Years of Experience')" />
                    <x-text-input wire:model="doctor_experience" id="doctor_experience" class="mt-1 block w-full" type="number" min="0" max="80" />
                    <x-input-error class="mt-2" :messages="$errors->get('doctor_experience')" />
                </div>

                <div>
                    <x-input-label for="doctor_bio" :value="__('Short Bio')" />
                    <textarea wire:model="doctor_bio" id="doctor_bio" rows="4" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('doctor_bio')" />
                </div>
            </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    wire:click="previousStep"
                    class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Back
                </button>
                <x-primary-button type="button" wire:click="register">
                    {{ $registrationType === 'doctor' ? 'Create Account & Submit Application' : 'Register as Patient' }}
                </x-primary-button>
            </div>
        </div>
        @endif
    </div>
</div>
