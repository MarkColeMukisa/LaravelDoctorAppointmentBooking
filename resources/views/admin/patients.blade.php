<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Patient Records') }}
        </h2>
    </x-slot>

    <livewire:admin-patient-records defer />
</x-app-layout>
