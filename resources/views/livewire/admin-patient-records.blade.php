<div id="patient-records" class="mx-auto max-w-[85rem] px-4 py-5 sm:px-6 lg:px-8 lg:py-7">
    <x-success-toast :livewire-only="true" />

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="grid gap-3 border-b border-gray-200 px-6 py-4 md:flex md:items-center md:justify-between dark:border-neutral-700">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-100">Patient Records</h3>
                <p class="text-sm text-gray-500 dark:text-neutral-400">Manage patient details and lifecycle statuses from one place.</p>
            </div>
            <div class="grid w-full gap-2 sm:grid-cols-3 md:w-auto">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="block w-full rounded-lg border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300"
                    placeholder="Search patient..."
                />
                <select
                    wire:model.live="statusFilter"
                    class="block w-full rounded-lg border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300"
                >
                    <option value="">All statuses</option>
                    @foreach ($patientStatuses as $status)
                        <option value="{{ $status }}">{{ \App\Models\User::patientStatusLabel($status) }}</option>
                    @endforeach
                </select>
                <select
                    wire:model.live="perPage"
                    class="block w-full rounded-lg border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300"
                >
                    <option value="10">10 / page</option>
                    <option value="25">25 / page</option>
                    <option value="50">50 / page</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 border-b border-gray-200 px-6 py-3 dark:border-neutral-700">
            <button
                type="button"
                wire:click="exportAuditCsv"
                class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
            >
                Export Audit CSV
            </button>
            <a
                href="{{ route('admin-patient-audits-print') }}"
                target="_blank"
                class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
            >
                Print / Save PDF
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-50 dark:bg-neutral-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Patient</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Address</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">DOB / Gender</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">System Fields</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Approval Trail</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Appointments</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Status Control</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                    @forelse ($patientRecords as $patient)
                        @php
                            $status = $patient->patient_status ?? \App\Models\User::PATIENT_STATUS_INACTIVE;
                            $statusMap = [
                                \App\Models\User::PATIENT_STATUS_ACTIVE => ['label' => 'Active', 'classes' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-500'],
                                \App\Models\User::PATIENT_STATUS_INACTIVE => ['label' => 'Inactive', 'classes' => 'bg-slate-200 text-slate-800 dark:bg-slate-500/10 dark:text-slate-300'],
                                \App\Models\User::PATIENT_STATUS_DECEASED => ['label' => 'Deceased', 'classes' => 'bg-rose-100 text-rose-800 dark:bg-rose-500/10 dark:text-rose-500'],
                                \App\Models\User::PATIENT_STATUS_TRANSFERRED => ['label' => 'Transferred', 'classes' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-500'],
                            ];
                            $statusMeta = $statusMap[$status] ?? $statusMap[\App\Models\User::PATIENT_STATUS_INACTIVE];
                            $latestAudit = $patient->latestPatientStatusAudit;
                            $latestRequest = $patient->latestPatientStatusChangeRequest;
                            $requestStatusMap = [
                                'pending' => ['label' => 'Pending Doctor Decision', 'classes' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-500'],
                                'approved' => ['label' => 'Approved', 'classes' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-500'],
                                'rejected' => ['label' => 'Rejected', 'classes' => 'bg-rose-100 text-rose-800 dark:bg-rose-500/10 dark:text-rose-500'],
                            ];
                        @endphp
                        <tr wire:key="patient-record-{{ $patient->id }}" class="align-top">
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $patient->name }}</p>
                                <p>{{ $patient->email }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">{{ $patient->contact_number ?: 'N/A' }}</td>
                            <td class="max-w-xs truncate px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">{{ $patient->address ?: 'N/A' }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                <p>{{ $patient->date_of_birth?->format('Y-m-d') ?: 'N/A' }}</p>
                                <p class="mt-1 text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">{{ $patient->gender ? \Illuminate\Support\Str::headline($patient->gender) : 'N/A' }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                <p>Registered: {{ $patient->registration_date?->format('Y-m-d') ?: $patient->created_at?->format('Y-m-d') }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">Created: {{ $patient->created_at?->format('Y-m-d H:i') }}</p>
                                <p class="text-xs text-gray-500 dark:text-neutral-500">Updated: {{ $patient->updated_at?->format('Y-m-d H:i') }}</p>
                            </td>
                            <td class="max-w-xs px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                @if ($latestRequest)
                                    @php $requestMeta = $requestStatusMap[$latestRequest->status] ?? $requestStatusMap['pending']; @endphp
                                    <span class="mb-2 inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide {{ $requestMeta['classes'] }}">{{ $requestMeta['label'] }}</span>
                                    <p class="text-xs text-gray-500 dark:text-neutral-500">Requested: {{ $latestRequest->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-neutral-500">Requested By: {{ $latestRequest->admin?->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-neutral-500">Doctor: {{ $latestRequest->doctor?->doctorUser?->name ?? 'N/A' }}</p>
                                    @if ($latestRequest->doctor_decision_note)
                                        <p class="mt-1 text-xs text-gray-700 dark:text-neutral-300">{{ \Illuminate\Support\Str::limit($latestRequest->doctor_decision_note, 90) }}</p>
                                    @endif
                                @endif

                                @if ($latestAudit)
                                    <p class="mt-2 text-xs text-gray-500 dark:text-neutral-500">Approved By Admin: {{ $latestAudit->admin?->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-neutral-500">Doctor: {{ $latestAudit->doctor?->doctorUser?->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-neutral-500">At: {{ $latestAudit->approved_at?->format('Y-m-d H:i') ?? 'N/A' }}</p>
                                @elseif (! $latestRequest)
                                    <span class="text-xs text-gray-500 dark:text-neutral-500">No approval history recorded yet.</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                <p>Total: {{ $patient->patient_appointments_count }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">Last: {{ $patient->latest_appointment_date ? \Illuminate\Support\Carbon::parse($patient->latest_appointment_date)->format('Y-m-d') : 'N/A' }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusMeta['classes'] }}">{{ $statusMeta['label'] }}</span>
                                <button
                                    type="button"
                                    wire:click="openStatusApprovalModal({{ $patient->id }})"
                                    class="mt-2 inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                >
                                    Request Status Change
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-neutral-400">
                                No patient records found for the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-6 py-4 dark:border-neutral-700">{{ $patientRecords->links() }}</div>
    </div>

    @if ($showStatusApprovalModal)
        <div wire:transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/50" wire:click="closeStatusApprovalModal"></div>
            <div class="relative z-10 w-full max-w-2xl rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <form wire:submit="submitStatusChangeRequest" class="space-y-4 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Create Doctor Approval Request</h3>
                            <p class="text-sm text-slate-600">Patient: <span class="font-semibold">{{ $selectedPatientName }}</span></p>
                            <p class="mt-1 text-xs text-slate-500">A doctor in-charge must approve this request before the patient status is changed.</p>
                        </div>
                        <button type="button" wire:click="closeStatusApprovalModal" class="text-slate-400 hover:text-slate-600">x</button>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Current Status</label>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ \App\Models\User::patientStatusLabel($selectedPatientCurrentStatus) }}</p>
                        </div>
                        <div>
                            <label for="pendingStatus" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Requested Status</label>
                            <select id="pendingStatus" wire:model="pendingStatus" class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach ($patientStatuses as $allowedStatus)
                                    <option value="{{ $allowedStatus }}">{{ \App\Models\User::patientStatusLabel($allowedStatus) }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('pendingStatus')" />
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Doctor In-Charge (Latest Appointment)</label>
                        <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-700">
                            <p class="font-semibold text-slate-900">{{ $selectedDoctorName }}</p>
                            <p class="text-xs text-slate-500">{{ $selectedDoctorEmail }}</p>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('selectedDoctorId')" />
                    </div>

                    <div>
                        <label for="adminRequestNote" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Admin Request Note</label>
                        <textarea
                            id="adminRequestNote"
                            wire:model="adminRequestNote"
                            rows="4"
                            class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Explain why this status change is required and include context for the doctor."
                        ></textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('adminRequestNote')" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeStatusApprovalModal" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Submit for Doctor Approval</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
