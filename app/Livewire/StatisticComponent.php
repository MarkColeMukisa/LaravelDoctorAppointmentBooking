<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Specialities;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class StatisticComponent extends Component
{
    public $users_count = 0;

    public $specialities_count = 0;

    public $doctors_count = 0;

    public $patients_count = 0;

    public $appointments_count = 0;

    public $doctor_appointments_count = 0;

    public $upcoming_appointments_count = 0;

    public $complete_appointments_count = 0;

    public $last_week_appointments_count = 0;

    public $last_month_appointments_count = 0;

    public $last_week_users_count = 0;

    public $last_month_users_count = 0;

    public $last_week_doctor_count = 0;

    public $last_month_doctor_count = 0;

    public $last_week_patient_count = 0;

    public $last_month_patient_count = 0;

    public function mount()
    {
        $now = Carbon::now();
        $today = Carbon::today();
        $lastWeek = $today->copy()->subWeek();
        $lastMonth = $today->copy()->subMonth();

        $this->users_count = User::count();
        $this->doctors_count = Doctor::count();
        $this->patients_count = User::where('role', User::ROLE_PATIENT)->count();
        $this->appointments_count = Appointment::count();
        $this->specialities_count = Specialities::count();

        $this->last_week_users_count = User::whereBetween('created_at', [$lastWeek, $now])->count();
        $this->last_month_users_count = User::whereBetween('created_at', [$lastMonth, $now])->count();
        $this->last_week_doctor_count = Doctor::whereBetween('created_at', [$lastWeek, $now])->count();
        $this->last_month_doctor_count = Doctor::whereBetween('created_at', [$lastMonth, $now])->count();
        $this->last_week_patient_count = User::where('role', User::ROLE_PATIENT)->whereBetween('created_at', [$lastWeek, $now])->count();
        $this->last_month_patient_count = User::where('role', User::ROLE_PATIENT)->whereBetween('created_at', [$lastMonth, $now])->count();

        if (auth()->user()->role == User::ROLE_DOCTOR) {
            $user_doctor = auth()->user();
            $doctor = Doctor::where('user_id', $user_doctor->id)->first();
            if ($doctor) {
                $doctorAppointments = Appointment::where('doctor_id', $doctor->id);

                $this->doctor_appointments_count = (clone $doctorAppointments)->count();
                $this->upcoming_appointments_count = (clone $doctorAppointments)->whereDate('appointment_date', '>', $today)->count();
                $this->complete_appointments_count = (clone $doctorAppointments)->whereDate('appointment_date', '<', $today)->count();
                $this->last_week_appointments_count = (clone $doctorAppointments)->whereBetween('appointment_date', [$lastWeek->toDateString(), $today->toDateString()])->count();
                $this->last_month_appointments_count = (clone $doctorAppointments)->whereBetween('appointment_date', [$lastMonth->toDateString(), $today->toDateString()])->count();
            }
        } else {
            $this->last_week_appointments_count = Appointment::whereBetween('created_at', [$lastWeek, $now])->count();
            $this->last_month_appointments_count = Appointment::whereBetween('created_at', [$lastMonth, $now])->count();
        }
    }

    public function render()
    {
        return view('livewire.statistic-component');
    }
}
