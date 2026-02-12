<?php

namespace App\Livewire;

use App\Mail\DoctorApplicationDecision;
use App\Models\Doctor;
use App\Models\DoctorApplication;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorApplications extends Component
{
    use WithPagination;

    public $perPage = 10;

    public $search = '';

    public function approve($id): void
    {
        $application = DoctorApplication::with('speciality')->find($id);
        if (! $application || $application->status !== DoctorApplication::STATUS_PENDING) {
            return;
        }

        $user = null;
        if ($application->user_id) {
            $user = User::find($application->user_id);
        }

        if (! $user) {
            $user = User::where('email', $application->email)->first();
        }

        if (! $user) {
            $user = new User;
            $user->name = $application->name;
            $user->email = $application->email;
            $user->role = User::ROLE_DOCTOR;
            $user->password = Hash::make(Str::random(32));
            $user->save();

            Password::sendResetLink(['email' => $user->email]);
        }

        if ($user->role === User::ROLE_ADMIN) {
            session()->flash('message', 'Admin accounts cannot be promoted to doctor.');

            return;
        }

        $user->role = User::ROLE_DOCTOR;
        $user->save();

        $doctor = Doctor::firstOrNew(['user_id' => $user->id]);
        $doctor->bio = $application->bio;
        $doctor->hospital_name = $application->hospital_name;
        $doctor->speciality_id = $application->speciality_id;
        $doctor->experience = $application->experience;
        $doctor->save();

        $application->status = DoctorApplication::STATUS_APPROVED;
        $application->user_id = $user->id;
        $application->save();

        $this->notifyApplicant($application, 'approved');
        session()->flash('message', 'Doctor application approved.');
    }

    public function reject($id): void
    {
        $application = DoctorApplication::find($id);
        if (! $application || $application->status !== DoctorApplication::STATUS_PENDING) {
            return;
        }

        $application->status = DoctorApplication::STATUS_REJECTED;
        $application->save();

        $this->notifyApplicant($application, 'rejected');
        session()->flash('message', 'Doctor application rejected.');
    }

    protected function notifyApplicant(DoctorApplication $application, string $decision): void
    {
        Mail::to($application->email)->send(new DoctorApplicationDecision([
            'name' => $application->name,
            'decision' => $decision,
            'email' => $application->email,
        ]));
    }

    public function render()
    {
        $query = DoctorApplication::with(['speciality', 'applicant'])
            ->orderByDesc('created_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('hospital_name', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.doctor-applications', [
            'applications' => $query->paginate($this->perPage),
        ]);
    }
}
