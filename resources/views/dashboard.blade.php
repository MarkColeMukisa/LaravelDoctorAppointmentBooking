<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Patient Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-200">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @php
                    $patientStatus = auth()->user()?->patient_status ?? \App\Models\User::PATIENT_STATUS_INACTIVE;
                    $statusMap = [
                        \App\Models\User::PATIENT_STATUS_ACTIVE => ['label' => 'Active', 'classes' => 'bg-emerald-100 text-emerald-800'],
                        \App\Models\User::PATIENT_STATUS_INACTIVE => ['label' => 'Inactive', 'classes' => 'bg-slate-200 text-slate-800'],
                        \App\Models\User::PATIENT_STATUS_DECEASED => ['label' => 'Deceased', 'classes' => 'bg-rose-100 text-rose-800'],
                        \App\Models\User::PATIENT_STATUS_TRANSFERRED => ['label' => 'Transferred', 'classes' => 'bg-amber-100 text-amber-800'],
                    ];
                    $statusMeta = $statusMap[$patientStatus] ?? $statusMap[\App\Models\User::PATIENT_STATUS_INACTIVE];
                @endphp
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="text-sm text-slate-600">Registration Date:
                            <span class="font-semibold text-slate-900">
                                {{ auth()->user()?->registration_date?->format('Y-m-d') ?? auth()->user()?->created_at?->format('Y-m-d') }}
                            </span>
                        </span>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusMeta['classes'] }}">
                            {{ $statusMeta['label'] }}
                        </span>
                    </div>
                </div>
                <livewire:home-availability-timeline />
                <livewire:featured-doctors :speciality_id="0"/>
                <livewire:specialist-cards/>
                <livewire:recent-appointments/>
            </div>
        </div>
    </div>
</x-app-layout>
