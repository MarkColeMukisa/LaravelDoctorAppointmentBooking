<section class="max-w-[85rem] px-4 py-8 sm:px-6 lg:px-8 lg:py-10 mx-auto">
    <div class="grid items-stretch gap-8 lg:grid-cols-2 lg:gap-10">
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <img
                class="h-full w-full object-cover aspect-[16/9] lg:aspect-auto"
                src="{{ asset('images/about.jpg') }}"
                alt="Doctor and patient consultation at {{ config('app.name', 'AmSam Clinic') }}"
            >
            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-gray-900/80 via-gray-900/30 to-transparent p-6 sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">Trusted care</p>
                <p class="mt-2 text-xl font-semibold text-white sm:text-2xl">{{ config('app.name', 'AmSam Clinic') }}</p>
                <p class="mt-2 max-w-md text-sm text-blue-50 sm:text-base">Compassionate doctors, modern scheduling, and dependable follow-up from first visit to recovery.</p>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">About us</p>
            <h2 class="mt-3 text-2xl font-bold text-gray-900 md:text-4xl md:leading-tight">Healthcare access that feels simple, personal, and reliable.</h2>
            <p class="mt-4 text-base leading-7 text-gray-600">
                We help patients connect with verified specialists, book appointments quickly, and stay informed before and after every consultation.
            </p>
            <div class="mt-6 space-y-4">
                <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <span class="mt-0.5 inline-flex size-8 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 3v18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 4h12l-2 4 2 4H6" />
                          </svg>
                          
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Mission</p>
                        <p class="mt-1 text-sm text-gray-600">To provide a seamless and efficient platform for patients to find and book appointments with verified doctors, ensuring a smooth and reliable healthcare experience.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <span class="mt-0.5 inline-flex size-8 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m7 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Vision</p>
                        <p class="mt-1 text-sm text-gray-600">To be the leading platform for healthcare appointments, trusted by patients and doctors alike, offering a seamless and reliable healthcare experience.</p>
                    </div>
                </div>
            </div>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ url('/all/doctors') }}" class="inline-flex items-center gap-x-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                    Explore doctors
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>
                <a href="{{ url('/register') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Create account
                </a>
            </div>
        </div>
    </div>
</section>

        </div>
       
      </div>
    </div>
  </div>