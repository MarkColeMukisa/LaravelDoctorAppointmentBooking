<x-app-layout>
      <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Booking Page') }}
        </h2>
    </x-slot>
    <livewire:booking-component :doctor="$doctor" :prefill-date="request()->query('date')" :prefill-slot="request()->query('slot')"/>
</x-app-layout>