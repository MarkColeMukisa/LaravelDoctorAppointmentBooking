<!-- Table Section -->
<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
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
                Doctors
              </h2>
              <p class="text-sm text-gray-600 dark:text-neutral-400">
                Our Registered Doctors.
              </p>
            </div>

            <div>
              <div class="inline-flex gap-x-2">
                <a class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none" href="/admin/create/doctor">
                  <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                  </svg>
                  Create
                </a>
              </div>
            </div>
          </div>
          <!-- End Header -->

          <div class="space-y-3 p-4 md:hidden">
            @forelse ($doctors as $item)
              <article wire:key="doctor-mobile-{{ $item->id }}" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $item->doctorUser->name }}</p>
                    <p class="text-xs text-slate-500">{{ $item->doctorUser->email }}</p>
                    <p class="mt-1 text-xs text-slate-600">{{ $item->speciality->speciality_name }} - {{ $item->hospital_name }}</p>
                  </div>
                  <button
                    type="button"
                    wire:click="featured({{ $item->id }})"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out {{ $item->is_featured ? 'bg-blue-600' : 'bg-gray-300' }}"
                    role="switch"
                    aria-checked="{{ $item->is_featured ? 'true' : 'false' }}"
                  >
                    <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 ease-in-out {{ $item->is_featured ? 'translate-x-5' : 'translate-x-0' }}"></span>
                  </button>
                </div>

                <p class="mt-3 text-xs text-slate-700">{{ \Illuminate\Support\Str::limit($item->bio, 130) }}</p>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                  <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
                    {{ $item->experience }} Years
                  </span>
                  <div class="flex items-center gap-3">
                    <a href="/edit/doctor/{{ $item->id }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Edit</a>
                    <button wire:confirm="Are you sure you want to delete this doctor?" wire:click="delete({{ $item->id }})" class="text-sm font-medium text-red-600 hover:text-red-800">Delete</button>
                  </div>
                </div>
              </article>
            @empty
              <p class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500">No data found!</p>
            @endforelse
          </div>

          <div class="hidden md:block">
          <!-- Table -->
          <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-start border-s border-gray-200 dark:border-neutral-700">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    S/N
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-start border-s border-gray-200 dark:border-neutral-700">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    Doctor
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-start border-s border-gray-200 dark:border-neutral-700">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    Bio
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-start border-s border-gray-200 dark:border-neutral-700">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    Details
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-start border-s border-gray-200 dark:border-neutral-700">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    Experience
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-start border-s border-gray-200 dark:border-neutral-700">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    Featured
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-start border-s border-gray-200 dark:border-neutral-700">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                    Actions
                  </span>
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              @if (count($doctors) > 0)
              @foreach ($doctors as $item)
              <tr>
                <td class="h-px w-auto whitespace-nowrap">
                  <div class="px-6 py-4">
                    <span class="text-sm font-medium text-gray-800 dark:text-neutral-200">{{$loop->iteration}}</span>
                  </div>
                </td>
                <td class="h-px w-auto whitespace-nowrap">
                  <div class="px-6 py-4">
                    <div class="flex items-center gap-x-3">
                      <div class="bg-gray-100 dark:bg-neutral-800 w-8 h-8 rounded-full flex items-center justify-center text-gray-500 font-bold">
                        {{ substr($item->doctorUser->name, 0, 1) }}
                      </div>
                      <div>
                        <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">{{$item->doctorUser->name}}</span>
                        <span class="block text-xs text-gray-500">{{$item->doctorUser->email}}</span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="h-px w-auto whitespace-nowrap">
                  <div class="px-6 py-4">
                    <span class="text-xs text-gray-500 dark:text-neutral-400 block max-w-[200px] truncate" title="{{$item->bio}}">
                      {{$item->bio}}
                    </span>
                  </div>
                </td>
                <td class="h-px w-auto whitespace-nowrap">
                  <div class="px-6 py-4">
                    <div class="flex flex-col">
                      <span class="text-sm font-medium text-gray-800 dark:text-neutral-200">{{$item->speciality->speciality_name}}</span>
                      <span class="text-xs text-gray-500">{{$item->hospital_name}}</span>
                    </div>
                  </div>
                </td>

                <td class="h-px w-auto whitespace-nowrap">
                  <div class="px-6 py-4">
                    <div class="inline-flex items-center gap-x-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-500">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3">
                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                      </svg>
                      {{$item->experience}} Years
                    </div>
                  </div>
                </td>
                <td class="h-px w-auto whitespace-nowrap">
                  <div class="px-6 py-4">
                    <!-- Toggle Switch -->
                    <button type="button"
                      wire:click="featured({{$item->id}})"
                      class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 {{ $item->is_featured ? 'bg-blue-600' : 'bg-gray-200' }}"
                      role="switch"
                      aria-checked="{{ $item->is_featured ? 'true' : 'false' }}">
                      <span class="sr-only">Use setting</span>
                      <span aria-hidden="true"
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $item->is_featured ? 'translate-x-5' : 'translate-x-0' }}">
                      </span>
                    </button>
                    <!-- End Toggle Switch -->
                  </div>
                </td>

                <td class="h-px w-auto whitespace-nowrap">
                  <div class="px-6 py-4">
                    <div class="flex items-center gap-x-3">
                      <a href="/edit/doctor/{{$item->id}}" class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-500 dark:hover:text-blue-400">Edit</a>
                      <button wire:confirm="Are you sure you want to delete this doctor?" wire:click="delete({{$item->id}})" class="text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-500 dark:hover:text-red-400">Delete</button>
                    </div>
                  </div>
                </td>
              </tr>
              @endforeach
              @else
              <tr>
                <td colspan="4">No data found!</td>
              </tr>
              @endif

            </tbody>
          </table>
          <!-- End Table -->
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Card -->
</div>
<!-- End Table Section -->
