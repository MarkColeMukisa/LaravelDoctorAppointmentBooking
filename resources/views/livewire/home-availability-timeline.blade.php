<div class="py-8 bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white border border-gray-200 shadow-sm rounded-2xl p-6 lg:p-8">
      <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
          <p class="text-sm uppercase tracking-wide text-blue-600 font-semibold">Live Availability</p>
          <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">Find the next open slot</h2>
          <p class="mt-2 text-gray-600 max-w-2xl">
            Pick a doctor (or view all) and a preferred day of the week to preview real-time availability pulled directly from schedules.
            Slots that are already booked are automatically hidden so patients only see what is truly open.
          </p>
        </div>
        <div class="w-full lg:w-auto grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium text-gray-700">Doctor</label>
            <select wire:model.live="selectedDoctorId"
              class="mt-1 py-2 px-3 w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
              <option value="all">All doctors</option>
              @forelse ($doctors as $doctor)
              <option value="{{ $doctor->id }}">
                {{ str($doctor->doctorUser->name)->startsWith('Dr.') ? $doctor->doctorUser->name : 'Dr. ' . $doctor->doctorUser->name }} • {{ $doctor->speciality->speciality_name }}
              </option>
              @empty
              <option value="">No doctors with schedules yet</option>
              @endforelse
            </select>

          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Preferred day</label>
            <select wire:model.live="preferredDay"
              class="mt-1 py-2 px-3 w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
              <option value="any">Any day</option>
              @foreach (\App\Models\Doctor::DAYS_OF_WEEK as $key => $day)
              <option value="{{ $key }}">{{ $day }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <div class="mt-8">
        @if (count($availableSlots))
        <div class="grid gap-4 md:grid-cols-2">
          @foreach ($availableSlots as $slot)
          @php
          $bookingDate = $slot['date']->toDateString();
          $bookingTime = $slot['from']->format('H:i:s');
          $bookingUrl = auth()->check()
          ? url("/booking/page/{$slot['doctor']->id}?date={$bookingDate}&slot={$bookingTime}")
          : url('/login');
          @endphp
          <div class="p-5 border rounded-xl shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-semibold text-blue-600">{{ $slot['day_label'] }}</p>
                <p class="text-lg font-bold text-gray-900">{{ $slot['date']->format('d M, Y') }}</p>
                <p class="text-sm text-gray-500">
                  {{ $slot['from']->format('h:i A') }} – {{ $slot['to']->format('h:i A') }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-sm font-semibold text-gray-800">
                  Dr. {{ $slot['doctor']->doctorUser->name }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ $slot['doctor']->speciality->speciality_name }}
                </p>
                <p class="text-xs text-gray-400">{{ $slot['doctor']->hospital_name }}</p>
              </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
              <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">
                <span class="size-2 rounded-full bg-emerald-500"></span>
                Slot open • tracked
              </span>
              <a href="{{ $bookingUrl }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg">
                {{ auth()->check() ? 'Book slot' : 'Sign in to book' }}
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="1.6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" />
                </svg>
              </a>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <div class="p-6 border border-dashed rounded-2xl text-center text-gray-500">
          No availability matches the current filters yet. Encourage doctors to add schedules from their dashboard
          so patients can start booking instantly.
        </div>
        @endif
      </div>
    </div>
  </div>
</div>