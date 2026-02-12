<footer class="border-t border-gray-200 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-900">{{ config('app.name', 'AmSam Clinic') }}</p>
                <p class="text-xs text-gray-500 mt-1">Trusted care, streamlined appointments.</p>
            </div>
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                <a class="hover:text-gray-900" href="{{ url('/') }}">Home</a>
                <a class="hover:text-gray-900" href="{{ url('/all/doctors') }}">Doctors</a>
                <a class="hover:text-gray-900" href="{{ url('/articles') }}">Articles</a>
                <a class="hover:text-gray-900" href="{{ url('/profile') }}">Profile</a>
            </div>
        </div>
        <div class="mt-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-xs text-gray-500">
            <span>© {{ date('Y') }} {{ config('app.name', 'AmSam Clinic') }}. All rights reserved.</span>
            <span>Support: support@example.com</span>
        </div>
    </div>
</footer>
