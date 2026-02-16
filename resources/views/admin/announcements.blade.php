<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Announcement Banners') }}
        </h2>
    </x-slot>

    <livewire:announcement-banner-manager defer />
</x-app-layout>
