<div>
  <div wire:loading>
    <div class="animate-spin inline-block size-6 border-[3px] border-current border-t-transparent text-blue-600 rounded-full dark:text-blue-500" role="status" aria-label="loading">
      <span class="sr-only">Loading...</span>
    </div>
    Processing..</div>
    <!-- Card Blog -->
<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-10 bg-white border my-2  shadow-md">
   <!-- Grid -->
  <div class="grid grid-cols-2 md:grid-cols-3 gap-8 md:gap-12">
    <div class="text-center">
      <livewire:profile-image :user_id="$doctor_details->doctorUser->id"/>
      <div class="mt-2 sm:mt-4">
        <h3 class="text-sm font-medium text-gray-800 sm:text-base lg:text-lg dark:text-neutral-200">
          {{$doctor_details->doctorUser->name}}
        </h3>
        <p class="text-xs text-gray-600 sm:text-sm lg:text-base dark:text-neutral-400">
          {{$doctor_details->speciality->speciality_name}} / {{$doctor_details->hospital_name}}
        </p>
      </div>
    </div>
    <!-- End Col -->
    <div class="text-center">
        <label for="">Select Appointment Type</label>
        <select wire:model="appointment_type" class="py-3 px-4 pe-9 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600">
            <option value="0">On site</option>
            <option value="1">Live consultation</option>
          </select>
        @error('appointment_type')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
            <h3>Select an Available Date</h3>
    <input type="text" id="datepicker" autocomplete="off" class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none bg-gray-100 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="Select Available date">
    @if($selectedDate)
        <div class="mt-3 p-3 rounded-lg border text-left">
            <p class="text-sm font-semibold text-gray-700">Tracking</p>
            <h4 class="text-lg font-bold text-gray-900">{{ \Carbon\Carbon::parse($selectedDate)->format('l, d M Y') }}</h4>
            @if ($highlightSlot)
                <p class="text-sm text-emerald-600 font-semibold">Suggested slot: {{ \Carbon\Carbon::parse($highlightSlot)->format('h:i A') }}</p>
            @endif
            @if ($selectedSlot)
                <p class="text-sm text-blue-600 font-semibold">Selected slot: {{ \Carbon\Carbon::parse($selectedSlot)->format('h:i A') }}</p>
            @endif
        </div>
    @endif
    @error('selectedDate')
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
    @enderror
    <div>
        <h2 class="text-xl font-bold mb-2">Available Time Slots</h2>
        <div class="flex flex-wrap gap-2 justify-center">
            @forelse ($timeSlots as $slot)
                <button class="@class([
                        'm-1 px-4 py-2 rounded-lg text-sm font-semibold transition',
                        $highlightSlot === $slot ? 'bg-emerald-600 text-white ring-2 ring-emerald-200' : 'bg-blue-500 text-white hover:bg-blue-700',
                    ])"
                    type="button"
                    wire:click="selectSlot('{{$slot}}')">
                    {{ date('h:i A',strtotime($slot)) }}
                </button>
            @empty
                @if ($selectedDate)
                    <p class="text-sm text-gray-500">No available slots for this date.</p>
                @else
                    <p class="text-sm text-gray-500">Pick a date to see the available timeline.</p>
                @endif
            @endforelse
        </div>
        @error('selectedSlot')
            <p class="text-xs text-red-500 mt-2 text-center">{{ $message }}</p>
        @enderror
        <div class="mt-4 flex justify-center">
            <button
                type="button"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none"
                wire:click="bookAppointment"
                wire:confirm="Are you sure you want to book this appointment?"
                @if (! $selectedDate || ! $selectedSlot) disabled @endif
            >
                Book Appointment
            </button>
        </div>
    </div>
    </div>
    <!-- End Col -->
  </div>
  <!-- End Grid -->
</div>
<!-- End Card Blog -->
<script src="pikaday.js"></script>
    <script>
        // Inject available dates from Livewire
            var availableDates = @json($availableDates);

            var picker = new Pikaday({
                field: document.getElementById('datepicker'),
                format: 'YYYY-MM-DD',
                onSelect: function(date) {
                    var selectedDate = picker.toString();
                    @this.call('selectDate', selectedDate);
                },
                disableDayFn: function(date) {
                    // Disable all dates not in the availableDates array
                    var formattedDate = moment(date).format('YYYY-MM-DD');
                    return !availableDates.includes(formattedDate);
                }
            });
    </script>

</div>
