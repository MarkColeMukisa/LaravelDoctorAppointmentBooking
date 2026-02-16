<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientStatusChangeRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPatientRecords extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public string $search = '';

    public string $statusFilter = '';

    public bool $showStatusApprovalModal = false;

    public ?int $selectedPatientId = null;

    public string $selectedPatientName = '';

    public string $selectedPatientCurrentStatus = '';

    public string $pendingStatus = '';

    public string $selectedDoctorId = '';

    public string $selectedDoctorName = '';

    public string $selectedDoctorEmail = '';

    public string $adminRequestNote = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openStatusApprovalModal(int $patientId): void
    {
        $admin = auth()->user();

        if (! $admin || $admin->role !== User::ROLE_ADMIN) {
            return;
        }

        $patient = User::query()
            ->where('id', $patientId)
            ->where('role', User::ROLE_PATIENT)
            ->first();

        if (! $patient) {
            return;
        }

        $latestDoctorAppointment = Appointment::query()
            ->where('patient_id', $patientId)
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->with('doctor.doctorUser:id,name,email')
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->orderByDesc('id')
            ->first();

        $latestDoctor = $latestDoctorAppointment?->doctor;
        if (! $latestDoctor instanceof Doctor) {
            session()->flash('message', 'No latest doctor assignment found for this patient. Add at least one appointment first.');

            return;
        }

        $this->resetValidation();
        $this->selectedPatientId = $patient->id;
        $this->selectedPatientName = $patient->name;
        $this->selectedPatientCurrentStatus = $patient->patient_status ?? User::PATIENT_STATUS_INACTIVE;
        $this->pendingStatus = $this->selectedPatientCurrentStatus;
        $this->selectedDoctorId = (string) $latestDoctor->id;
        $this->selectedDoctorName = $latestDoctor->doctorUser?->name ?? 'Unknown Doctor';
        $this->selectedDoctorEmail = $latestDoctor->doctorUser?->email ?? 'N/A';
        $this->adminRequestNote = '';
        $this->showStatusApprovalModal = true;
    }

    public function closeStatusApprovalModal(): void
    {
        $this->showStatusApprovalModal = false;
        $this->selectedPatientId = null;
        $this->selectedPatientName = '';
        $this->selectedPatientCurrentStatus = '';
        $this->pendingStatus = '';
        $this->selectedDoctorId = '';
        $this->selectedDoctorName = '';
        $this->selectedDoctorEmail = '';
        $this->adminRequestNote = '';
        $this->resetValidation();
    }

    public function submitStatusChangeRequest(): void
    {
        $admin = auth()->user();

        if (! $admin || $admin->role !== User::ROLE_ADMIN) {
            return;
        }

        $validated = $this->validate([
            'selectedPatientId' => ['required', 'integer', 'exists:users,id'],
            'pendingStatus' => ['required', 'in:'.implode(',', User::PATIENT_STATUSES)],
            'selectedDoctorId' => ['required', 'integer', 'exists:doctors,id'],
            'adminRequestNote' => ['required', 'string', 'min:15', 'max:4000'],
        ], [
            'adminRequestNote.min' => 'Provide a sufficiently detailed request note (minimum 15 characters).',
        ]);

        $patient = User::query()
            ->where('id', $validated['selectedPatientId'])
            ->where('role', User::ROLE_PATIENT)
            ->first();

        if (! $patient) {
            $this->addError('selectedPatientId', 'Selected patient no longer exists.');

            return;
        }

        $latestDoctorAppointment = Appointment::query()
            ->where('patient_id', $patient->id)
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->orderByDesc('id')
            ->first(['doctor_id']);

        if (! $latestDoctorAppointment || ! $latestDoctorAppointment->doctor_id) {
            $this->addError('selectedDoctorId', 'No latest doctor assignment found for this patient.');

            return;
        }

        $latestDoctorId = (int) $latestDoctorAppointment->doctor_id;
        if ($latestDoctorId !== (int) $validated['selectedDoctorId']) {
            $this->addError('selectedDoctorId', 'The latest doctor assignment changed. Refresh and submit again.');

            return;
        }

        $currentStatus = $patient->patient_status ?? User::PATIENT_STATUS_INACTIVE;
        if ($currentStatus === $validated['pendingStatus']) {
            $this->addError('pendingStatus', 'Choose a new status different from the current one.');

            return;
        }

        $pendingRequestExists = PatientStatusChangeRequest::query()
            ->where('patient_id', $patient->id)
            ->where('status', PatientStatusChangeRequest::STATUS_PENDING)
            ->exists();

        if ($pendingRequestExists) {
            $this->addError('pendingStatus', 'A pending status change request already exists for this patient.');

            return;
        }

        PatientStatusChangeRequest::query()->create([
            'patient_id' => $patient->id,
            'admin_id' => $admin->id,
            'doctor_id' => (int) $validated['selectedDoctorId'],
            'current_status' => $currentStatus,
            'requested_status' => $validated['pendingStatus'],
            'status' => PatientStatusChangeRequest::STATUS_PENDING,
            'admin_request_note' => $validated['adminRequestNote'],
        ]);

        $this->closeStatusApprovalModal();
        session()->flash('message', 'Status change request submitted. Waiting for doctor approval.');
    }

    public function exportAuditCsv(): StreamedResponse
    {
        $admin = auth()->user();

        abort_unless($admin && $admin->role === User::ROLE_ADMIN, 403);

        $filename = 'patient-status-audits-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'patient_name',
                'patient_email',
                'current_status',
                'requested_status',
                'decision_status',
                'admin_name',
                'doctor_name',
                'decided_at',
                'admin_request_note',
                'doctor_decision_note',
            ]);

            PatientStatusChangeRequest::query()
                ->with(['patient:id,name,email', 'admin:id,name', 'doctor.doctorUser:id,name'])
                ->whereIn('status', [
                    PatientStatusChangeRequest::STATUS_APPROVED,
                    PatientStatusChangeRequest::STATUS_REJECTED,
                ])
                ->latest('decided_at')
                ->chunk(200, function ($requests) use ($output): void {
                    foreach ($requests as $request) {
                        fputcsv($output, [
                            $request->patient?->name ?? '',
                            $request->patient?->email ?? '',
                            $request->current_status,
                            $request->requested_status,
                            $request->status,
                            $request->admin?->name ?? '',
                            $request->doctor?->doctorUser?->name ?? '',
                            $request->decided_at?->format('Y-m-d H:i:s') ?? '',
                            preg_replace("/\r|\n/", ' ', (string) $request->admin_request_note),
                            preg_replace("/\r|\n/", ' ', (string) $request->doctor_decision_note),
                        ]);
                    }
                });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function render(): View
    {
        $admin = auth()->user();

        $patientRecords = User::query()
            ->select([
                'id',
                'name',
                'email',
                'contact_number',
                'address',
                'date_of_birth',
                'gender',
                'registration_date',
                'patient_status',
                'created_at',
                'updated_at',
            ])
            ->where('role', User::ROLE_PATIENT)
            ->when($admin?->role !== User::ROLE_ADMIN, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('patient_status', $this->statusFilter);
            })
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $searchTerm = '%'.$this->search.'%';
                    $subQuery->where('name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm)
                        ->orWhere('contact_number', 'like', $searchTerm)
                        ->orWhere('address', 'like', $searchTerm);
                });
            })
            ->with([
                'latestPatientStatusAudit.admin:id,name',
                'latestPatientStatusAudit.doctor.doctorUser:id,name',
                'latestPatientStatusChangeRequest.admin:id,name',
                'latestPatientStatusChangeRequest.doctor.doctorUser:id,name',
            ])
            ->withCount('patientAppointments')
            ->withMax('patientAppointments as latest_appointment_date', 'appointment_date')
            ->latest('created_at')
            ->paginate($this->perPage);

        return view('livewire.admin-patient-records', [
            'patientRecords' => $patientRecords,
            'patientStatuses' => User::PATIENT_STATUSES,
        ]);
    }
}
