<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientStatusAudit;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorPatientRecords extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public string $search = '';

    public string $statusFilter = '';

    public bool $showStatusUpdateModal = false;

    public ?int $selectedPatientId = null;

    public string $selectedPatientName = '';

    public string $selectedPatientCurrentStatus = '';

    public string $pendingStatus = '';

    public string $doctorApprovalNote = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openStatusUpdateModal(int $patientId): void
    {
        $doctorUser = auth()->user();
        if (! $doctorUser || $doctorUser->role !== User::ROLE_DOCTOR) {
            return;
        }

        $doctor = Doctor::query()
            ->where('user_id', $doctorUser->id)
            ->first();

        if (! $doctor) {
            return;
        }

        $patient = User::query()
            ->where('id', $patientId)
            ->where('role', User::ROLE_PATIENT)
            ->whereHas('patientAppointments', function (Builder $query) use ($doctor): void {
                $query->where('doctor_id', $doctor->id);
            })
            ->first();

        if (! $patient) {
            return;
        }

        $this->resetValidation();
        $this->selectedPatientId = $patient->id;
        $this->selectedPatientName = $patient->name;
        $this->selectedPatientCurrentStatus = $patient->patient_status ?? User::PATIENT_STATUS_INACTIVE;
        $this->pendingStatus = $this->selectedPatientCurrentStatus;
        $this->doctorApprovalNote = '';
        $this->showStatusUpdateModal = true;
    }

    public function closeStatusUpdateModal(): void
    {
        $this->showStatusUpdateModal = false;
        $this->selectedPatientId = null;
        $this->selectedPatientName = '';
        $this->selectedPatientCurrentStatus = '';
        $this->pendingStatus = '';
        $this->doctorApprovalNote = '';
        $this->resetValidation();
    }

    public function updatePatientStatus(): void
    {
        $doctorUser = auth()->user();
        if (! $doctorUser || $doctorUser->role !== User::ROLE_DOCTOR) {
            return;
        }

        $validated = $this->validate([
            'selectedPatientId' => ['required', 'integer', 'exists:users,id'],
            'pendingStatus' => ['required', 'in:'.implode(',', User::PATIENT_STATUSES)],
            'doctorApprovalNote' => ['required', 'string', 'min:15', 'max:4000'],
        ], [
            'doctorApprovalNote.min' => 'Please provide a clear reason for the status change (minimum 15 characters).',
        ]);

        $doctor = Doctor::query()
            ->where('user_id', $doctorUser->id)
            ->first();

        if (! $doctor) {
            return;
        }

        $patient = User::query()
            ->where('id', $validated['selectedPatientId'])
            ->where('role', User::ROLE_PATIENT)
            ->whereHas('patientAppointments', function (Builder $query) use ($doctor): void {
                $query->where('doctor_id', $doctor->id);
            })
            ->first();

        if (! $patient) {
            $this->addError('selectedPatientId', 'Selected patient was not found in your patient list.');

            return;
        }

        $currentStatus = $patient->patient_status ?? User::PATIENT_STATUS_INACTIVE;
        if ($currentStatus === $validated['pendingStatus']) {
            $this->addError('pendingStatus', 'Choose a new status different from the current one.');

            return;
        }

        DB::transaction(function () use ($patient, $doctor, $currentStatus, $validated): void {
            $patient->refresh();
            $patient->patient_status = $validated['pendingStatus'];
            if (! $patient->registration_date) {
                $patient->registration_date = $patient->created_at?->toDateString() ?? now()->toDateString();
            }
            $patient->save();

            PatientStatusAudit::query()->create([
                'patient_id' => $patient->id,
                'admin_id' => null,
                'doctor_id' => $doctor->id,
                'previous_status' => $currentStatus,
                'new_status' => $validated['pendingStatus'],
                'doctor_approval_note' => $validated['doctorApprovalNote'],
                'doctor_approval_granted' => true,
                'approved_at' => now(),
            ]);
        });

        $this->closeStatusUpdateModal();
        session()->flash('message', 'Patient status updated successfully.');
    }

    public function render(): View
    {
        $doctorUser = auth()->user();
        $doctor = null;

        if ($doctorUser && $doctorUser->role === User::ROLE_DOCTOR) {
            $doctor = Doctor::query()
                ->where('user_id', $doctorUser->id)
                ->first();
        }

        $patientRecords = User::query()
            ->select([
                'id',
                'name',
                'email',
                'contact_number',
                'date_of_birth',
                'patient_status',
                'created_at',
                'updated_at',
            ])
            ->where('role', User::ROLE_PATIENT)
            ->when(! $doctor, function (Builder $query): void {
                $query->whereRaw('1 = 0');
            })
            ->when($doctor, function (Builder $query) use ($doctor): void {
                $query->whereHas('patientAppointments', function (Builder $appointmentQuery) use ($doctor): void {
                    $appointmentQuery->where('doctor_id', $doctor->id);
                });
            })
            ->when($this->statusFilter !== '', function (Builder $query): void {
                $query->where('patient_status', $this->statusFilter);
            })
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $subQuery): void {
                    $searchTerm = '%'.$this->search.'%';
                    $subQuery
                        ->where('name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm)
                        ->orWhere('contact_number', 'like', $searchTerm);
                });
            })
            ->with(['latestPatientStatusAudit.doctor.doctorUser:id,name'])
            ->withCount([
                'patientAppointments as doctor_appointments_count' => function (Builder $query) use ($doctor): void {
                    if (! $doctor) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('doctor_id', $doctor->id);
                },
            ])
            ->addSelect([
                'latest_doctor_appointment_date' => Appointment::query()
                    ->selectRaw('MAX(appointment_date)')
                    ->whereColumn('patient_id', 'users.id')
                    ->when($doctor, function (Builder $query) use ($doctor): void {
                        $query->where('doctor_id', $doctor->id);
                    }),
            ])
            ->latest('created_at')
            ->paginate($this->perPage);

        return view('livewire.doctor-patient-records', [
            'patientRecords' => $patientRecords,
            'patientStatuses' => User::PATIENT_STATUSES,
        ]);
    }
}
