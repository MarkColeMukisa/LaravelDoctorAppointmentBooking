@component('mail::message')
# Doctor Application Received

Hi {{ $applicationData['name'] }},

Thanks for applying to become a doctor. Your application has been received and is now under review.

### Application Summary
- **Speciality:** {{ $applicationData['speciality_name'] }}

We will notify you once an admin reviews your request.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
