<div class="py-6">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white border border-gray-200 shadow-sm rounded-2xl p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <p class="text-sm uppercase font-semibold tracking-wider text-blue-600">Availability Planner</p>
          <h2 class="text-2xl font-bold text-gray-900">Control what patients see</h2>
          <p class="text-gray-600 mt-2 max-w-2xl">
            Craft precise availability windows, keep them updated in seconds, and help patients book confidently.
            Every change you make here is reflected instantly on the public booking experience and dashboard widgets.
          </p>
        </div>
        @if ($doctor)
          @php
            $nextAvailability = $doctor->nextAvailability();
          @endphp
          <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 w-full lg:w-72">
            <p class="text-sm font-semibold text-blue-700">Next public slot</p>
            @if ($nextAvailability)
              <p class="text-lg font-bold text-gray-900 mt-1">
                {{ $nextAvailability['date']->format('D, d M') }}
              </p>
              <p class="text-sm text-gray-600">
                {{ $nextAvailability['from']->format('h:i A') }} – {{ $nextAvailability['to']->format('h:i A') }}
              </p>
              <p class="text-xs text-gray-500 mt-1">
                {{ $nextAvailability['day_label'] }} • {{ $doctor->hospital_name }}
              </p>
            @else
              <p class="text-sm text-gray-600 mt-2">Add a schedule to unlock bookings.</p>
            @endif
          </div>
        @endif
      </div>

      @if (session()->has('availability_message'))
        <div class="mt-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm">
          {{ session('availability_message') }}
        </div>
      @endif

      @if (! $doctor)
        <div class="mt-6 p-4 border border-dashed rounded-xl text-sm text-gray-600">
          Your doctor profile is not linked yet. Please contact the administrator to ensure your account is assigned to a doctor profile.
        </div>
      @else
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
          <div class="lg:col-span-1 border border-gray-200 rounded-xl p-5">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ $editingScheduleId ? 'Edit slot' : 'Add a new slot' }}
            </h3>
            <p class="text-sm text-gray-500 mb-4">
              Define a day and time window. Slots are validated to avoid overlaps automatically.
            </p>
            <div class="space-y-4">
              <div>
                <label class="text-sm font-medium text-gray-700">
                  Day of the week
                </label>
                <select wire:model.defer="form.available_day"
                  class="mt-1 py-2 px-3 w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                  <option value="">Select day</option>
                  @foreach ($daysOfWeek as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                  @endforeach
                </select>
                @error('form.available_day')
                  <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-sm font-medium text-gray-700">From</label>
                  <input type="time" wire:model.defer="form.from"
                    class="mt-1 py-2 px-3 w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                  @error('form.from')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                  @enderror
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700">To</label>
                  <input type="time" wire:model.defer="form.to"
                    class="mt-1 py-2 px-3 w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                  @error('form.to')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>
              <div class="flex items-center gap-3">
                <button type="button" wire:click="save"
                  class="inline-flex justify-center items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2">
                  {{ $editingScheduleId ? 'Update slot' : 'Save slot' }}
                </button>
                @if ($editingScheduleId)
                  <button type="button" wire:click="resetForm"
                    class="text-sm font-semibold text-gray-500 hover:text-gray-700">
                    Cancel
                  </button>
                @endif
              </div>
            </div>
          </div>

          <div class="lg:col-span-2 border border-gray-200 rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Your weekly blueprint</h3>
                <p class="text-sm text-gray-500">Edit or remove slots inline. Any change instantly updates patient views.</p>
              </div>
              <span class="text-xs font-semibold text-gray-500 uppercase">Tracked slots: {{ $schedules->count() }}</span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="py-3">Day</th>
                    <th class="py-3">From</th>
                    <th class="py-3">To</th>
                    <th class="py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  @forelse ($schedules as $schedule)
                    <tr>
                      <td class="py-3 text-sm font-medium text-gray-800">
                        {{ $daysOfWeek[$schedule->available_day] ?? 'Day' }}
                      </td>
                      <td class="py-3 text-sm text-gray-600">{{ \Carbon\Carbon::createFromFormat('H:i:s', $schedule->from)->format('h:i A') }}</td>
                      <td class="py-3 text-sm text-gray-600">{{ \Carbon\Carbon::createFromFormat('H:i:s', $schedule->to)->format('h:i A') }}</td>
                      <td class="py-3 text-sm text-right space-x-2">
                        <button type="button" wire:click="edit({{ $schedule->id }})"
                          class="inline-flex items-center px-3 py-1 rounded-lg border text-blue-600 border-blue-100 hover:bg-blue-50">
                          Edit
                        </button>
                        <button type="button" wire:click="delete({{ $schedule->id }})"
                          wire:confirm="Remove this slot? Appointments booked inside this window remain intact."
                          class="inline-flex items-center px-3 py-1 rounded-lg border text-red-600 border-red-100 hover:bg-red-50">
                          Delete
                        </button>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="py-6 text-center text-sm text-gray-500">
                        No schedules yet. Add at least one slot so patients can start booking you.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>
</div>

