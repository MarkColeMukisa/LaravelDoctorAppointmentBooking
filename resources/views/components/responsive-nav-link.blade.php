@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg px-4 py-2.5 text-start text-base font-semibold text-blue-700 bg-blue-50 focus:outline-none focus:bg-blue-100 transition duration-150 ease-in-out'
            : 'block w-full rounded-lg px-4 py-2.5 text-start text-base font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 focus:outline-none focus:bg-slate-50 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
