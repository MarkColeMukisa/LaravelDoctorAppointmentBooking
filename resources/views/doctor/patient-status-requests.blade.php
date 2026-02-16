<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Patient Status Requests') }}
        </h2>
    </x-slot>

    <livewire:doctor-patient-status-requests defer />
</x-app-layout>
