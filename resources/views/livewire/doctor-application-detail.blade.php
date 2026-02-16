<div class="max-w-4xl mx-auto px-4 py-8">
  <x-success-toast :livewire-only="true" />

  @if (! $application)
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm text-sm text-gray-600">
      Application not found.
    </div>
  @else
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-semibold text-gray-900">{{ $application->name }}</h2>
          <p class="text-sm text-gray-500">{{ $application->email }}</p>
        </div>
        <div>
          @php
            $statusMap = [
                'pending' => ['label' => 'Pending', 'classes' => 'bg-yellow-100 text-yellow-800'],
                'approved' => ['label' => 'Approved', 'classes' => 'bg-emerald-100 text-emerald-800'],
                'rejected' => ['label' => 'Rejected', 'classes' => 'bg-rose-100 text-rose-800'],
            ];
            $statusMeta = $statusMap[$application->status] ?? $statusMap['pending'];
          @endphp
          <span wire:transition.opacity class="py-1 px-2 rounded-full text-xs font-semibold {{ $statusMeta['classes'] }}">
            {{ $statusMeta['label'] }}
          </span>
        </div>
      </div>

      <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <p class="text-xs uppercase text-gray-500">Hospital / Clinic</p>
          <p class="text-sm font-medium text-gray-900">{{ $application->hospital_name }}</p>
        </div>
        <div>
          <p class="text-xs uppercase text-gray-500">Speciality</p>
          <p class="text-sm font-medium text-gray-900">{{ $application->speciality?->speciality_name ?? 'N/A' }}</p>
        </div>
        <div>
          <p class="text-xs uppercase text-gray-500">Experience</p>
          <p class="text-sm font-medium text-gray-900">{{ $application->experience }} years</p>
        </div>
        <div>
          <p class="text-xs uppercase text-gray-500">Applicant User</p>
          <p class="text-sm font-medium text-gray-900">
            {{ $application->applicant?->name ?? 'Not linked' }}
          </p>
        </div>
      </div>

      <div class="mt-6">
        <p class="text-xs uppercase text-gray-500">Bio</p>
        <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $application->bio }}</p>
      </div>

      <div class="mt-6 flex items-center gap-3">
        @if ($application->status === 'pending')
          <button class="py-2 px-4 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700" wire:confirm="Approve this doctor application?" wire:click="approve">
            Approve
          </button>
          <button class="py-2 px-4 rounded-lg text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700" wire:confirm="Reject this doctor application?" wire:click="reject">
            Reject
          </button>
        @else
          <span class="text-sm text-gray-500">No actions available.</span>
        @endif
      </div>
    </div>
  @endif
</div>
