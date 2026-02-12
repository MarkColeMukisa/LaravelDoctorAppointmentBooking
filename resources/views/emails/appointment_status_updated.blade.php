@component('mail::message')
# Appointment Status Updated

Dear {{ $appointmentData['recipient_name'] }},

The appointment status has changed.

### Status Update
- **Previous Status:** {{ str_replace('_', ' ', ucfirst($appointmentData['previous_status'])) }}
- **New Status:** {{ str_replace('_', ' ', ucfirst($appointmentData['new_status'])) }}
- **Updated By:** {{ $appointmentData['updated_by'] }}

### Appointment Details:
- **Date:** {{ $appointmentData['date'] }}
- **Time:** {{ $appointmentData['time'] }}
- **Location:** {{ $appointmentData['location'] }}

### Patient Details:
- **Name:** {{ $appointmentData['patient_name'] }}
- **Email:** {{ $appointmentData['patient_email'] }}

### Doctor Details:
- **Name:** {{ $appointmentData['doctor_name'] }}
- **Specialization:** {{ $appointmentData['doctor_specialization'] }}

@if($appointmentData['recipient_role'] == 'admin')
## Admin Notification
You are receiving this update because the appointment status changed in your system.
@endif

@if($appointmentData['recipient_role'] == 'doctor')
## Doctor Notification
Your appointment status update has been recorded.
@endif

@if($appointmentData['recipient_role'] == 'patient')
## Patient Notification
Your appointment status has changed. Please review the details above.
@endif

@component('mail::button', ['url' => 'http://127.0.0.1:8000/login'])
View Appointment
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
