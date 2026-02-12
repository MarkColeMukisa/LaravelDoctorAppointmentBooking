<?php

namespace App\Livewire;

use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentStatusUpdated;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class AllAppointments extends Component
{
    use WithPagination;

    public $perPage = 5;

    public $search = '';

    public function updateStatus($id, $status): void
    {
        $user = auth()->user();
        if (! $user || $user->role !== User::ROLE_DOCTOR) {
            return;
        }

        if (! in_array($status, Appointment::STATUSES, true)) {
            return;
        }

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (! $doctor) {
            return;
        }

        $appointment = Appointment::where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (! $appointment) {
            return;
        }

        $currentStatus = $appointment->status
            ?? ($appointment->is_complete == 1 ? Appointment::STATUS_COMPLETED : Appointment::STATUS_PENDING);

        if ($currentStatus === $status) {
            return;
        }

        $appointment->status = $status;
        $appointment->is_complete = $status === Appointment::STATUS_COMPLETED ? 1 : 0;
        $appointment->save();

        $patient = User::find($appointment->patient_id);
        $doctorModel = Doctor::find($appointment->doctor_id);

        $appointmentEmailData = [
            'date' => $appointment->appointment_date,
            'time' => Carbon::parse($appointment->appointment_time)->format('H:i A'),
            'location' => '123 Medical Street, Health City',
            'patient_name' => $patient?->name,
            'patient_email' => $patient?->email,
            'doctor_name' => $doctorModel?->doctorUser?->name,
            'doctor_email' => $doctorModel?->doctorUser?->email,
            'doctor_specialization' => $doctorModel?->speciality?->speciality_name,
            'previous_status' => $currentStatus,
            'new_status' => $status,
            'updated_by' => $user->name,
        ];

        $this->sendAppointmentStatusNotification($appointmentEmailData);
        session()->flash('message', 'Appointment status updated successfully.');
    }

    public function cancel($id)
    {
        $cancelled_by_details = auth()->user();
        $appointment = Appointment::find($id);

        $patient = User::find($appointment->patient_id);
        $doctor = Doctor::find($appointment->doctor_id);

        $appointmentEmailData = [
            'date' => $appointment->appointment_date,
            'time' => Carbon::parse($appointment->appointment_time)->format('H:i A'),
            'location' => '123 Medical Street, Health City',
            'patient_name' => $patient->name,
            'patient_email' => $patient->email,
            'doctor_name' => $doctor->doctorUser->name,
            'doctor_email' => $doctor->doctorUser->email,
            'doctor_specialization' => $doctor->speciality->speciality_name,
            'cancelled_by' => $cancelled_by_details->name,
            'role' => $cancelled_by_details->role,
        ];
        // dd($appointmentEmailData);
        $this->sendAppointmentNotification($appointmentEmailData);

        if ($appointment) {
            $appointment->status = Appointment::STATUS_CANCELLED;
            $appointment->is_complete = 0;
            $appointment->save();
        }

        session()->flash('message', 'Appointment cancelled successfully');
        if (auth()->user()->role == User::ROLE_PATIENT) {
            return $this->redirect('/my/appointments', navigate: true);
        }

        if (auth()->user()->role == User::ROLE_ADMIN) {
            return $this->redirect('/admin/appointments', navigate: true);
        }

        if (auth()->user()->role == User::ROLE_DOCTOR) {
            return $this->redirect('/doctor/appointments', navigate: true);
        }
    }

    public function start($appointment_id)
    {
        if (auth()->user() && auth()->user()->role == User::ROLE_DOCTOR) {
            $this->updateStatus($appointment_id, Appointment::STATUS_IN_PROGRESS);
        }
        $this->redirect('/live_consultation', navigate: true);
    }

    public function sendAppointmentNotification($appointmentData)
    {
        // Send to Admin
        $appointmentData['recipient_name'] = 'Admin Admin';
        $appointmentData['recipient_role'] = 'admin';
        Mail::to('admin@example.com')->send(new AppointmentCancelled($appointmentData));

        // Send to Doctor
        $appointmentData['recipient_name'] = $appointmentData['doctor_name'];
        $appointmentData['recipient_role'] = 'doctor';
        Mail::to($appointmentData['doctor_email'])->send(new AppointmentCancelled($appointmentData));

        // Send to Patient
        $appointmentData['recipient_name'] = $appointmentData['patient_name'];
        $appointmentData['recipient_role'] = 'patient';
        Mail::to($appointmentData['patient_email'])->send(new AppointmentCancelled($appointmentData));

        return 'Appointment notifications sent successfully!';
    }

    public function sendAppointmentStatusNotification($appointmentData)
    {
        // Send to Admin
        $appointmentData['recipient_name'] = 'Admin Admin';
        $appointmentData['recipient_role'] = 'admin';
        Mail::to('admin@example.com')->send(new AppointmentStatusUpdated($appointmentData));

        // Send to Doctor
        $appointmentData['recipient_name'] = $appointmentData['doctor_name'];
        $appointmentData['recipient_role'] = 'doctor';
        Mail::to($appointmentData['doctor_email'])->send(new AppointmentStatusUpdated($appointmentData));

        // Send to Patient
        $appointmentData['recipient_name'] = $appointmentData['patient_name'];
        $appointmentData['recipient_role'] = 'patient';
        Mail::to($appointmentData['patient_email'])->send(new AppointmentStatusUpdated($appointmentData));
    }

    public function render()
    {
        $user = auth()->user();

        if (auth()->user() && auth()->user()->role == User::ROLE_DOCTOR) {

            $doctor = Doctor::where('user_id', $user->id)->first();

            return view('livewire.all-appointments', [
                'all_appointments' => Appointment::search($this->search)
                    ->with('patient', 'doctor')
                    ->where('doctor_id', $doctor->id)
                    ->paginate($this->perPage),
            ]);
        }
        if (auth()->user() && auth()->user()->role == User::ROLE_PATIENT) {

            return view('livewire.all-appointments', [
                'all_appointments' => Appointment::search($this->search)
                    ->with('patient', 'doctor')
                    ->where('patient_id', $user->id)
                    ->paginate($this->perPage),
            ]);
        }

        return view('livewire.all-appointments', [
            'all_appointments' => Appointment::search($this->search)
                ->with('patient', 'doctor')
                ->paginate($this->perPage),
        ]);
    }
}
