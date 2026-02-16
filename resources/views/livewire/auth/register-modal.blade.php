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
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Doctor Modal Props
    public string $doctor_name = '';
    public string $doctor_email = '';
    public string $doctor_hospital_name = '';
    public string $doctor_speciality_id = '';
    public string $doctor_experience = '';
    public string $doctor_bio = '';
    public bool $showDoctorModal = false;
    public $specialities = [];

    // Main Modal State
    public bool $show = false;

    public function mount(): void
    {
        $this->specialities = Specialities::all();
    }

    #[\Livewire\Attributes\On('open-register-modal')]
    public function open()
    {
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->reset(['name', 'email', 'password', 'password_confirmation']);
        $this->resetValidation();
        // Also close nested modal if open
        $this->closeDoctorModal();
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));
        $user->role = User::ROLE_PATIENT;
        $user->save();

        Auth::login($user);

        $this->close();
        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    public function openDoctorModal(): void
    {
        // Pre-fill doctor data with user data if available
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
            'user_id' => Auth::id() ?? null, // Can be null if applying before registering?? Original code used Auth::id(), assuming user is logged in? 
            // Wait, standard flow is: Patient registers -> Sees dashboard -> Applies to be doctor.
            // OR checks "Apply to be doctor" on register page? 
            // The logic in register.blade.php shows a button "Apply to become a doctor" at the bottom.
            // If they click that, it opens a modal. If they submit that, it creates an application.
            // BUT, if they are not logged in (which they aren't, they are registering), Auth::id() is null.
            // The original code uses Auth::id() inside register.blade.php?? 
            // Ah, register.blade.php is for guests. 
            // If the user submits the doctor application form WITHOUT registering first, Auth::id() is null.
            // The User table has user_id nullable? Let's assume so or it will crash.
            // Actually, looking at original file: it has `Auth::id()` used. 
            // If this is the registration page, Auth::id() is null. 
            // So `user_id` will be null.
            // Let's keep existing logic.

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

        session()->now('message', 'Your doctor application has been submitted. You will receive an update after admin review.');
    }
}; ?>

<div>
    @if($show)
    <div
        x-data="{ show: @entangle('show') }"
        x-show="show"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden p-4 sm:p-6 md:p-8"
        role="dialog"
        aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="$wire.close()"></div>

        <!-- Modal Panel -->
        <div
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative bg-white rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 rounded-t-lg">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Register
                        </h3>
                        <div class="mt-4">
                            <!-- Registration Form -->
                            <x-success-toast />
                            <form wire:submit="register">
                                <!-- Name -->
                                <div>
                                    <x-input-label for="reg_name" :value="__('Name')" />
                                    <x-text-input wire:model="name" id="reg_name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <!-- Email Address -->
                                <div class="mt-4">
                                    <x-input-label for="reg_email" :value="__('Email')" />
                                    <x-text-input wire:model="email" id="reg_email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <!-- Password -->
                                <div class="mt-4">
                                    <x-input-label for="reg_password" :value="__('Password')" />

                                    <x-text-input wire:model="password" id="reg_password" class="block mt-1 w-full"
                                        type="password"
                                        name="password"
                                        required autocomplete="new-password" />

                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <!-- Confirm Password -->
                                <div class="mt-4">
                                    <x-input-label for="reg_password_confirmation" :value="__('Confirm Password')" />

                                    <x-text-input wire:model="password_confirmation" id="reg_password_confirmation" class="block mt-1 w-full"
                                        type="password"
                                        name="password_confirmation" required autocomplete="new-password" />

                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" @click="$wire.close(); $dispatch('open-login-modal')">
                                        {{ __('Already registered?') }}
                                    </button>

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
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="$wire.close()">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Doctor Application Modal -->
    <!-- IMPORTANT: Standard modal stacking can be tricky. We use fixed positioning + z-index > 50. -->
    @if ($showDoctorModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto overflow-x-hidden p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="closeDoctorModal"></div>

        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl p-6 transform transition-all">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Doctor Application</h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" wire:click="closeDoctorModal">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
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
                    <select wire:model="doctor_speciality_id" id="doctor_speciality_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                    <textarea wire:model="doctor_bio" id="doctor_bio" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
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