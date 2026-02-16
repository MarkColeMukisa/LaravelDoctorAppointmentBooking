<?php

namespace App\Livewire;

use App\Models\Doctor;
use App\Models\PatientStatusAudit;
use App\Models\PatientStatusChangeRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorPatientStatusRequests extends Component
{
    use WithPagination;

    public string $statusFilter = PatientStatusChangeRequest::STATUS_PENDING;

    public int $perPage = 10;

    public bool $showDecisionModal = false;

    public ?int $selectedRequestId = null;

    public string $selectedDecision = '';

    public string $doctorDecisionNote = '';

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openDecisionModal(int $requestId, string $decision): void
    {
        $doctorUser = auth()->user();
        if (! $doctorUser || $doctorUser->role !== User::ROLE_DOCTOR) {
            return;
        }

        if (! in_array($decision, [PatientStatusChangeRequest::STATUS_APPROVED, PatientStatusChangeRequest::STATUS_REJECTED], true)) {
            return;
        }

        $doctor = Doctor::query()->where('user_id', $doctorUser->id)->first();
        if (! $doctor) {
            return;
        }

        $request = PatientStatusChangeRequest::query()
            ->where('id', $requestId)
            ->where('doctor_id', $doctor->id)
            ->where('status', PatientStatusChangeRequest::STATUS_PENDING)
            ->first();

        if (! $request) {
            return;
        }

        $this->resetValidation();
        $this->selectedRequestId = $request->id;
        $this->selectedDecision = $decision;
        $this->doctorDecisionNote = '';
        $this->showDecisionModal = true;
    }

    public function closeDecisionModal(): void
    {
        $this->showDecisionModal = false;
        $this->selectedRequestId = null;
        $this->selectedDecision = '';
        $this->doctorDecisionNote = '';
        $this->resetValidation();
    }

    public function confirmDecision(): void
    {
        $doctorUser = auth()->user();
        if (! $doctorUser || $doctorUser->role !== User::ROLE_DOCTOR) {
            return;
        }

        $validated = $this->validate([
            'selectedRequestId' => ['required', 'integer', 'exists:patient_status_change_requests,id'],
            'selectedDecision' => ['required', 'in:'.PatientStatusChangeRequest::STATUS_APPROVED.','.PatientStatusChangeRequest::STATUS_REJECTED],
            'doctorDecisionNote' => ['required', 'string', 'min:15', 'max:4000'],
        ], [
            'doctorDecisionNote.min' => 'Please provide a clear clinical or operational reason (minimum 15 characters).',
        ]);

        $doctor = Doctor::query()->where('user_id', $doctorUser->id)->first();
        if (! $doctor) {
            return;
        }

        $requestResolved = false;

        DB::transaction(function () use ($validated, $doctor, &$requestResolved): void {
            $request = PatientStatusChangeRequest::query()
                ->where('id', $validated['selectedRequestId'])
                ->where('doctor_id', $doctor->id)
                ->where('status', PatientStatusChangeRequest::STATUS_PENDING)
                ->lockForUpdate()
                ->first();

            if (! $request) {
                return;
            }

            $request->status = $validated['selectedDecision'];
            $request->doctor_decision_note = $validated['doctorDecisionNote'];
            $request->decided_at = now();
            $request->save();
            $requestResolved = true;

            if ($validated['selectedDecision'] === PatientStatusChangeRequest::STATUS_APPROVED) {
                $patient = User::query()
                    ->where('id', $request->patient_id)
                    ->where('role', User::ROLE_PATIENT)
                    ->first();

                if ($patient) {
                    $patient->patient_status = $request->requested_status;
                    if (! $patient->registration_date) {
                        $patient->registration_date = $patient->created_at?->toDateString() ?? now()->toDateString();
                    }
                    $patient->save();

                    PatientStatusAudit::query()->create([
                        'patient_id' => $patient->id,
                        'admin_id' => $request->admin_id,
                        'doctor_id' => $doctor->id,
                        'previous_status' => $request->current_status,
                        'new_status' => $request->requested_status,
                        'doctor_approval_note' => $validated['doctorDecisionNote'],
                        'doctor_approval_granted' => true,
                        'approved_at' => now(),
                    ]);
                }
            }
        });

        if (! $requestResolved) {
            $this->addError('selectedRequestId', 'This request is no longer pending.');

            return;
        }

        $this->closeDecisionModal();
        session()->flash('message', 'Status change request decision recorded successfully.');
    }

    public function render(): View
    {
        $doctorUser = auth()->user();
        $doctor = null;

        if ($doctorUser && $doctorUser->role === User::ROLE_DOCTOR) {
            $doctor = Doctor::query()->where('user_id', $doctorUser->id)->first();
        }

        $requests = PatientStatusChangeRequest::query()
            ->when(! $doctor, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->when($doctor, function ($query) use ($doctor) {
                $query->where('doctor_id', $doctor->id);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->with([
                'patient:id,name,email',
                'admin:id,name',
            ])
            ->latest('created_at')
            ->paginate($this->perPage);

        return view('livewire.doctor-patient-status-requests', [
            'requests' => $requests,
            'requestStatuses' => PatientStatusChangeRequest::STATUSES,
        ]);
    }
}
