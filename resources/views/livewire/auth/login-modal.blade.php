<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;

new class extends Component
{
    public LoginForm $form;
    public bool $show = false;

    public function mount()
    {
        //
    }

    #[\Livewire\Attributes\On('open-login-modal')]
    public function open()
    {
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->form->reset();
        $this->resetValidation();
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();
        Session::flash('status', __('Logged in successfully.'));

        $this->close();

        // Redirect based on role, similar to the original login page
        if (auth()->user()->role == 0) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        } elseif (auth()->user()->role == 1) {
            $this->redirectIntended(default: route('doctor-dashboard', absolute: false), navigate: true);
        } elseif (auth()->user()->role == 2) {
            $this->redirectIntended(default: route('admin-dashboard', absolute: false), navigate: true);
        }
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
            class="relative bg-white rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-md">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 rounded-t-lg">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Log in
                        </h3>
                        <div class="mt-4">
                            <!-- Session Status -->
                            <x-auth-session-status class="mb-4" :status="session('status')" />

                            <form wire:submit="login">
                                <!-- Email Address -->
                                <div>
                                    <x-input-label for="login_email" :value="__('Email')" />
                                    <x-text-input wire:model="form.email" id="login_email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
                                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                                </div>

                                <!-- Password -->
                                <div class="mt-4">
                                    <x-input-label for="login_password" :value="__('Password')" />

                                    <x-text-input wire:model="form.password" id="login_password" class="block mt-1 w-full"
                                        type="password"
                                        name="password"
                                        required autocomplete="current-password" />

                                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                                </div>

                                <!-- Remember Me -->
                                <div class="block mt-4">
                                    <label for="remember" class="inline-flex items-center">
                                        <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                                        <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                                    </label>
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    @if (Route::has('password.request'))
                                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                                        {{ __('Forgot your password?') }}
                                    </a>
                                    @endif

                                    <x-primary-button class="ms-3">
                                        {{ __('Log in') }}
                                    </x-primary-button>
                                </div>
                            </form>
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
</div>