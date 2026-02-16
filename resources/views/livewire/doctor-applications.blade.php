<!-- Table Section -->
<div class="max-w-[85rem] px-4 py-5 sm:px-6 lg:px-8 lg:py-7 mx-auto">
  <x-success-toast :livewire-only="true" />
  <div wire:loading>
    <div class="animate-spin inline-block size-6 border-[3px] border-current border-t-transparent text-blue-600 rounded-full dark:text-blue-500" role="status" aria-label="loading">
      <span class="sr-only">Loading...</span>
    </div>
    Processing..
  </div>
  <!-- Card -->
  <div class="flex flex-col">
    <div class="-m-1.5 overflow-x-auto">
      <div class="p-1.5 min-w-full inline-block align-middle">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
          <!-- Header -->
          <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
            <div>
              <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                Doctor Applications
              </h2>
              <p class="text-sm text-gray-600 dark:text-neutral-400">
                Review and approve doctor applications.
              </p>
            </div>

            <div class="sm:col-span-1">
              <label for="doctor-app-search" class="sr-only">Search</label>
              <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" id="doctor-app-search" name="search" class="py-2 px-3 ps-11 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="Search">
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4">
                  <svg class="flex-shrink-0 size-4 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
              </div>
            </div>
          </div>
          <!-- End Header -->

          <div class="space-y-3 p-4 md:hidden">
            @forelse ($applications as $application)
              @php
                $statusMap = [
                    'pending' => ['label' => 'Pending', 'classes' => 'bg-yellow-100 text-yellow-800'],
                    'approved' => ['label' => 'Approved', 'classes' => 'bg-emerald-100 text-emerald-800'],
                    'rejected' => ['label' => 'Rejected', 'classes' => 'bg-rose-100 text-rose-800'],
                ];
                $statusMeta = $statusMap[$application->status] ?? $statusMap['pending'];
              @endphp
              <article wire:key="doctor-app-mobile-{{ $application->id }}" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-start justify-between gap-2">
                  <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $application->name }}</p>
                    <p class="text-xs text-slate-500">{{ $application->email }}</p>
                    <p class="mt-1 text-xs text-slate-600">{{ $application->hospital_name }}</p>
                    <p class="text-xs text-slate-600">{{ $application->speciality?->speciality_name ?? 'N/A' }} - {{ $application->experience }} years</p>
                  </div>
                  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide {{ $statusMeta['classes'] }}">
                    {{ $statusMeta['label'] }}
                  </span>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                  <a class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100" href="{{ route('admin-doctor-application-detail', $application->id) }}">
                    View
                  </a>
                  @if ($application->status === 'pending')
                    <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700" wire:confirm="Approve this doctor application?" wire:click="approve({{ $application->id }})">
                      Approve
                    </button>
                    <button class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700" wire:confirm="Reject this doctor application?" wire:click="reject({{ $application->id }})">
                      Reject
                    </button>
                  @endif
                </div>
              </article>
            @empty
              <p class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500">No doctor applications found.</p>
            @endforelse
          </div>

          <div class="hidden md:block">
          <!-- Table -->
          <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
              <tr>
                <th scope="col" class="px-6 py-3 text-start">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    Applicant
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-start">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    Hospital
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-start">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    Speciality
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-start">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    Experience
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-start">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    Status
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-start">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    Actions
                  </span>
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              @forelse ($applications as $application)
                @php
                  $statusMap = [
                      'pending' => ['label' => 'Pending', 'classes' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-500'],
                      'approved' => ['label' => 'Approved', 'classes' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-500'],
                      'rejected' => ['label' => 'Rejected', 'classes' => 'bg-rose-100 text-rose-800 dark:bg-rose-500/10 dark:text-rose-500'],
                  ];
                  $statusMeta = $statusMap[$application->status] ?? $statusMap['pending'];
                @endphp
                <tr wire:key="doctor-app-{{ $application->id }}" class="bg-white hover:bg-gray-50 dark:bg-neutral-900 dark:hover:bg-neutral-800">
                  <td class="size-px whitespace-nowrap align-top">
                    <div class="p-6">
                      <div class="text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ $application->name }}</div>
                      <div class="text-xs text-gray-500 dark:text-neutral-400">{{ $application->email }}</div>
                    </div>
                  </td>
                  <td class="size-px whitespace-nowrap align-top">
                    <div class="p-6 text-sm text-gray-600 dark:text-neutral-400">{{ $application->hospital_name }}</div>
                  </td>
                  <td class="size-px whitespace-nowrap align-top">
                    <div class="p-6 text-sm text-gray-600 dark:text-neutral-400">{{ $application->speciality?->speciality_name ?? 'N/A' }}</div>
                  </td>
                  <td class="size-px whitespace-nowrap align-top">
                    <div class="p-6 text-sm text-gray-600 dark:text-neutral-400">{{ $application->experience }} years</div>
                  </td>
                  <td class="size-px whitespace-nowrap align-top">
                    <div class="p-6">
                      <span wire:transition.opacity class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium rounded-full {{ $statusMeta['classes'] }}">
                        {{ $statusMeta['label'] }}
                      </span>
                    </div>
                  </td>
                  <td class="size-px whitespace-nowrap align-top">
                    <div class="p-6 flex gap-2">
                      @if ($application->status === 'pending')
                        <a class="py-1.5 px-3 rounded-lg text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100" href="{{ route('admin-doctor-application-detail', $application->id) }}">
                          View
                        </a>
                        <button class="py-1.5 px-3 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700" wire:confirm="Approve this doctor application?" wire:click="approve({{ $application->id }})">
                          Approve
                        </button>
                        <button class="py-1.5 px-3 rounded-lg text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700" wire:confirm="Reject this doctor application?" wire:click="reject({{ $application->id }})">
                          Reject
                        </button>
                      @else
                        <a class="py-1.5 px-3 rounded-lg text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100" href="{{ route('admin-doctor-application-detail', $application->id) }}">
                          View
                        </a>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="p-6 text-sm text-gray-500">No doctor applications found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
          <!-- End Table -->
          </div>

          <!-- Footer -->
          <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-t border-gray-200 dark:border-neutral-700">
            <div class="max-w-sm space-y-3">
              <select wire:model.live='perPage' class="py-2 px-3 pe-9 block border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
            </div>

            {{ $applications->links() }}
          </div>
          <!-- End Footer -->
        </div>
      </div>
    </div>
  </div>
  <!-- End Card -->
</div>
