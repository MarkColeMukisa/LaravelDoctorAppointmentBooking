@props([
    'message' => session('message'),
    'livewireOnly' => false,
])

@if ($message && (! $livewireOnly || request()->header('X-Livewire')))
    <div x-data="{ show: false, timeoutId: null }"
        x-show="show"
        x-cloak
        x-transition:enter="transform ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transform ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        x-init="
            requestAnimationFrame(() => {
                show = true;
                timeoutId = setTimeout(() => show = false, 6000);
            });
        "
        class="fixed top-24 right-4 z-[100] max-w-sm rounded-lg bg-emerald-600 px-4 py-3 text-sm text-white shadow-lg"
        role="alert">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <div>
                <span class="block font-semibold">Success</span>
                <span class="block">{{ $message }}</span>
            </div>
            <button
                @click="
                    show = false;
                    if (timeoutId) {
                        clearTimeout(timeoutId);
                    }
                "
                class="ml-auto text-white/80 hover:text-white"
                type="button"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
@endif
