<!-- Table Section -->
<div class="max-w-[85rem] px-4 py-5 sm:px-6 lg:px-8 lg:py-7 mx-auto">
  <div class="my-2">
      <h5 class="text-gray-500 ">Recent Appointments</h5>
  </div>
  <!-- Card -->
  <div class="flex flex-col">
    <div class="-m-1.5 overflow-x-auto">
      <div class="p-1.5 min-w-full inline-block align-middle">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
          <!-- Table -->
          <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
              
              <tr>
                @if (auth()->user() && auth()->user()->role == 0)
                
                @else
                    <th scope="col" class="px-6 py-3 text-start">
                    <div class="flex items-center gap-x-2">
                      <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                          Patient Name
                      </span>
                    </div>
                  </th>
                @endif
                
                  @if (auth()->user() && auth()->user()->role == 1)

                  @else
                    <th scope="col" class="px-6 py-3 text-start">
                      <div class="flex items-center gap-x-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                          Doctor
                        </span>
                      </div>
                    </th>
                  @endif
               

                <th scope="col" class="px-6 py-3 text-start">
                  <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                      Appointment Date
                    </span>
                  </div>
                </th>

                <th scope="col" class="px-6 py-3 text-start">
                  <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                      Appointment Time
                    </span>
                  </div>
                </th>

                <th scope="col" class="px-6 py-3 text-start">
                  <div class="flex items-center gap-x-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                      Status
                    </span>
                  </div>
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
               @if (count($recent_appointments) > 0)
                  @foreach ($recent_appointments as $appointment)
                    <tr class="bg-white hover:bg-gray-50 dark:bg-neutral-900 dark:hover:bg-neutral-800">
                        @if (auth()->user() && auth()->user()->role == 0)
                      @else
                        <td class="size-px whitespace-nowrap align-top">
                          <a class="block p-6" href="#">
                            <div class="flex items-center gap-x-4">
                              <livewire:profile-image :user_id="$appointment->patient->id"/>
                              <div>
                                <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ $appointment->patient->name}}</span>
                              </div>
                            </div>
                          </a>
                        </td>
                        @endif
                         @if (auth()->user() && auth()->user()->role == 1)
                         @else
                          <td class="size-px whitespace-nowrap align-top">
                            <a class="block p-6" href="#">
                              <div class="flex items-center gap-x-3">
                              <livewire:profile-image :user_id="$appointment->doctor->doctorUser->id"/>
                                <div class="grow">
                                  <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">{{$appointment->doctor->doctorUser->name}}</span>
                                  <span class="block text-sm text-gray-500 dark:text-neutral-500">{{$appointment->doctor->doctorUser->email}}</span>
                                </div>
                              </div>
                            </a>
                          </td>
                         @endif
                        
                        <td class="h-px w-72 min-w-72 align-top">
                            <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ date('d M Y',strtotime($appointment->appointment_date))}}</span>
                        </td>
                        <td class="size-px whitespace-nowrap align-top">
                          <div class="block p-6" href="#">
                            <span class="text-sm text-gray-600 dark:text-neutral-400">{{ date('H:i A',strtotime($appointment->appointment_time))}}</span>
                          </div>
                        </td>
                        <td class="size-px whitespace-nowrap align-top">
                          @php
                              $status = $appointment->status ?? ($appointment->is_complete == 1 ? 'completed' : 'pending');
                              $statusMap = [
                                  'pending' => ['label' => 'Pending', 'classes' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-500'],
                                  'in_progress' => ['label' => 'In Progress', 'classes' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-500'],
                                  'completed' => ['label' => 'Completed', 'classes' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-500'],
                                  'cancelled' => ['label' => 'Cancelled', 'classes' => 'bg-rose-100 text-rose-800 dark:bg-rose-500/10 dark:text-rose-500'],
                              ];
                              $statusMeta = $statusMap[$status] ?? $statusMap['pending'];
                          @endphp
                          <div class="block p-6">
                            <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium rounded-full {{ $statusMeta['classes'] }}">
                              {{ $statusMeta['label'] }}
                            </span>
                          </div>
                        </td>
                      </tr>
                  @endforeach
              @else
                  <tr>
                    <td colspan="5">No data Found!</td>
                  </tr>
              @endif
             
            </tbody>
          </table>
          <!-- End Table -->
        </div>
      </div>
    </div>
  </div>
  <!-- End Card -->
</div>
<!-- End Table Section -->
