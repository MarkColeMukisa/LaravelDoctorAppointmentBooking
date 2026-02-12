<?php

namespace App\Livewire;

use App\Mail\AppointmentCreated;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class BookingComponent extends Component
{
    public $doctor_details;

    public $appointment_type = 0;

    public $selectedDate;

    public $availableDates = [];

    public $timeSlots = [];

    public $prefillDate;

    public $prefillSlot;

    public $highlightSlot;

    public $selectedSlot;

    public function mount($doctor, $prefillDate = null, $prefillSlot = null)
    {
        $this->doctor_details = $doctor;
        $this->prefillDate = $prefillDate;
        $this->prefillSlot = $prefillSlot;

        $this->fetchAvailableDates($this->doctor_details);

        if ($this->prefillDate && in_array($this->prefillDate, $this->availableDates, true)) {
            $this->selectedDate = $this->prefillDate;
            $this->fetchTimeSlots($this->prefillDate, $this->doctor_details);
            if ($this->prefillSlot) {
                $this->highlightSlot = $this->prefillSlot;
                $this->selectedSlot = $this->prefillSlot;
            }
        }
    }

    public function bookAppointment()
    {
        $this->validate([
            'appointment_type' => 'required|in:0,1',
            'selectedDate' => 'required|date',
            'selectedSlot' => 'required',
        ], [
            'appointment_type.required' => 'Select an appointment type before booking.',
            'selectedDate.required' => 'Please pick a date from the calendar.',
            'selectedSlot.required' => 'Please select a time slot to continue.',
        ]);

        $carbonDate = Carbon::parse($this->selectedDate)->format('Y-m-d');
        $slot = $this->selectedSlot;
        $newAppointment = new Appointment;
        $newAppointment->patient_id = auth()->user()->id;
        $newAppointment->doctor_id = $this->doctor_details->id;
        $newAppointment->appointment_date = $carbonDate;
        $newAppointment->appointment_time = $slot;
        $newAppointment->appointment_type = $this->appointment_type;
        $newAppointment->status = Appointment::STATUS_PENDING;
        $newAppointment->save();

        $appointmentEmailData = [
            'date' => $this->selectedDate,
            'time' => Carbon::parse($slot)->format('H:i A'),
            'location' => '123 Medical Street, Health City',
            'patient_name' => auth()->user()->name,
            'patient_email' => auth()->user()->email,
            'doctor_name' => $this->doctor_details->doctorUser->name,
            'doctor_email' => $this->doctor_details->doctorUser->email,
            'appointment_type' => $this->appointment_type == 0 ? 'on-site' : 'live consultation',
            'doctor_specialization' => $this->doctor_details->speciality->speciality_name,
        ];
        // dd($appointmentEmailData);
        $this->sendAppointmentNotification($appointmentEmailData);

        session()->flash('message', 'appointment with Dr.'.$this->doctor_details->doctorUser->name.' on '.$this->selectedDate.$slot.' was created!');

        return $this->redirect('/my/appointments', navigate: true);
    }

    public function fetchAvailableDates($doctor)
    {
        $schedules = DoctorSchedule::where('doctor_id', $doctor->id)
            ->get();

        $availability = [];
        foreach ($schedules as $schedule) {
            $dayOfWeek = $schedule->available_day; // 0 - sunday
            $fromTime = Carbon::createFromFormat('H:i:s', $schedule->from);
            $toTime = Carbon::createFromFormat('H:i:s', $schedule->to);
            $availability[$dayOfWeek] = [
                'from' => $fromTime,
                'to' => $toTime,
            ];
        }

        $dates = [];
        $today = Carbon::today();
        for ($i = 0; $i < 365; $i++) { // 1 year
            $date = $today->copy()->addDays($i);
            $dayOfWeek = $date->dayOfWeek;

            if (isset($availability[$dayOfWeek])) {
                $dates[] = $date->format('Y-m-d');
            }
        }

        $this->availableDates = $dates;
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->highlightSlot = null;
        $this->selectedSlot = null;
        $this->fetchTimeSlots($date, $this->doctor_details);
    }

    public function selectSlot($slot): void
    {
        $this->selectedSlot = $slot;
        $this->highlightSlot = $slot;
    }

    public function fetchTimeSlots($date, $doctor)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek; // 0 , 1... 5
        $carbonDate = Carbon::parse($date)->format('Y-m-d');
        $schedule = DoctorSchedule::where('doctor_id', $doctor->id)
            ->where('available_day', $dayOfWeek)
            ->first();

        if ($schedule) {
            $fromTime = Carbon::createFromFormat('H:i:s', $schedule->from);
            $toTime = Carbon::createFromFormat('H:i:s', $schedule->to);

            $slots = [];
            while ($fromTime->lessThan($toTime)) {

                $timeSlot = $fromTime->format('H:i:s');
                $appointmentExists = Appointment::where('doctor_id', $doctor->id)
                    ->where('appointment_date', $carbonDate)
                    ->where('appointment_time', $timeSlot)
                    ->where(function ($query) {
                        $query->whereNull('status')
                            ->orWhere('status', '!=', Appointment::STATUS_CANCELLED);
                    })
                    ->exists();
                if (! $appointmentExists) {
                    $slots[] = $timeSlot;
                }

                $fromTime->addHour();
            }

            $this->timeSlots = $slots;
            if ($this->prefillSlot && in_array($this->prefillSlot, $this->timeSlots, true)) {
                $this->highlightSlot = $this->prefillSlot;
                $this->selectedSlot = $this->prefillSlot;
            }
            // dd($this->timeSlots);

        } else {
            $this->timeSlots = [];
            $this->highlightSlot = null;
            $this->selectedSlot = null;
        }
    }

    public function sendAppointmentNotification($appointmentData)
    {
        // Send to Admin
        $appointmentData['recipient_name'] = 'Admin Admin';
        $appointmentData['recipient_role'] = 'admin';
        Mail::to('admin@example.com')->send(new AppointmentCreated($appointmentData));

        // Send to Doctor
        $appointmentData['recipient_name'] = $appointmentData['doctor_name'];
        $appointmentData['recipient_role'] = 'doctor';
        Mail::to($appointmentData['doctor_email'])->send(new AppointmentCreated($appointmentData));

        // Send to Patient
        $appointmentData['recipient_name'] = $appointmentData['patient_name'];
        $appointmentData['recipient_role'] = 'patient';
        Mail::to($appointmentData['patient_email'])->send(new AppointmentCreated($appointmentData));

        return 'Appointment notifications sent successfully!';
    }

    public function render()
    {
        return view('livewire.booking-component', [
            'availableDates' => $this->availableDates,
        ]);
    }
}
