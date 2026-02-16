<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Doctor Dashboard') }}
        </h2>
    </x-slot>
    <livewire:statistic-component defer />
    <livewire:doctor-availability-panel defer />
    <livewire:recent-appointments defer />
</x-app-layout>
