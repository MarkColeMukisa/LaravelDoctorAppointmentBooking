@component('mail::message')
# New Doctor Application

A new doctor application has been submitted and needs review.

### Applicant Details
- **Name:** {{ $applicationData['name'] }}
- **Email:** {{ $applicationData['email'] }}
- **Hospital:** {{ $applicationData['hospital_name'] }}
- **Speciality:** {{ $applicationData['speciality_name'] }}
- **Experience:** {{ $applicationData['experience'] }} years

### Bio
{{ $applicationData['bio'] }}

@component('mail::button', ['url' => $applicationData['review_url']])
Review Application
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
