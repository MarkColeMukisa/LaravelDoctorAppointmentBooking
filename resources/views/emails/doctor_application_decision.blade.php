@component('mail::message')
# Doctor Application Update

Hi {{ $decisionData['name'] }},

Your doctor application has been **{{ $decisionData['decision'] }}**.

@if ($decisionData['decision'] === 'approved')
You can now log in and access your doctor dashboard.
@else
If you believe this was a mistake, please contact support.
@endif

@component('mail::button', ['url' => url('/login')])
Login
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
