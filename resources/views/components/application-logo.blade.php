<div {{ $attributes->merge(['class' => 'group flex items-center gap-4']) }}>
    <div class="relative flex size-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-50 to-cyan-50 ring-1 ring-blue-200 shadow-sm transition-transform duration-300 group-hover:scale-105">
        <span class="absolute inset-0 rounded-2xl ring-1 ring-white/70"></span>
        <img src="{{ asset('images/medics-mark.svg') }}" alt="{{ config('app.name', 'AmSam Clinic') }} logo" class="relative size-12">
    </div>
    <div class="text-start">
        <span class="block text-xl font-bold leading-tight tracking-tight text-slate-900 sm:text-2xl">
            {{ config('app.name', 'AmSam Clinic') }}
        </span>
        <span class="mt-0.5 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 sm:text-xs">
            Personal care, modern medicine
        </span>
    </div>
</div>
