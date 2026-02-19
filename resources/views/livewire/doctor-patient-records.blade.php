<div class="mx-auto max-w-[85rem] px-4 py-5 sm:px-6 lg:px-8 lg:py-7">
    <x-success-toast :livewire-only="true" />

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="grid gap-3 border-b border-gray-200 px-6 py-4 md:flex md:items-center md:justify-between dark:border-neutral-700">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-100">My Patients</h3>
                <p class="text-sm text-gray-500 dark:text-neutral-400">Manage patient statuses for patients assigned to your appointments.</p>
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

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-50 dark:bg-neutral-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Patient</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Age</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Appointments</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Current Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Latest Audit</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Action</th>
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
                        @endphp
                        <tr wire:key="doctor-patient-record-{{ $patient->id }}" class="align-top">
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $patient->name }}</p>
                                <p>{{ $patient->email }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                {{ $patient->contact_number ?: 'N/A' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                {{ $patient->age !== null ? $patient->age.' yrs' : 'N/A' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                <p>Total: {{ $patient->doctor_appointments_count }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">
                                    Last: {{ $patient->latest_doctor_appointment_date ? \Illuminate\Support\Carbon::parse($patient->latest_doctor_appointment_date)->format('Y-m-d') : 'N/A' }}
                                </p>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusMeta['classes'] }}">{{ $statusMeta['label'] }}</span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                @if ($latestAudit)
                                    <p class="text-xs text-gray-500 dark:text-neutral-500">Status: {{ \App\Models\User::patientStatusLabel($latestAudit->previous_status) }} -> {{ \App\Models\User::patientStatusLabel($latestAudit->new_status) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-neutral-500">By: {{ $latestAudit->doctor?->doctorUser?->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-neutral-500">At: {{ $latestAudit->approved_at?->format('Y-m-d H:i') ?? 'N/A' }}</p>
                                @else
                                    <span class="text-xs text-gray-500 dark:text-neutral-500">No audit history recorded yet.</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm">
                                <button
                                    type="button"
                                    wire:click="openStatusUpdateModal({{ $patient->id }})"
                                    class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                >
                                    Update Status
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-neutral-400">
                                No patient records found for the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-6 py-4 dark:border-neutral-700">{{ $patientRecords->links() }}</div>
    </div>

    @if ($showStatusUpdateModal)
        <div wire:transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/50" wire:click="closeStatusUpdateModal"></div>
            <div class="relative z-10 w-full max-w-xl rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <form wire:submit="updatePatientStatus" class="space-y-4 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Update Patient Status</h3>
                            <p class="text-sm text-slate-600">Patient: <span class="font-semibold">{{ $selectedPatientName }}</span></p>
                            <p class="mt-1 text-xs text-slate-500">Only status updates are allowed. Every change is permanently recorded in the audit trail.</p>
                        </div>
                        <button type="button" wire:click="closeStatusUpdateModal" class="text-slate-400 hover:text-slate-600">x</button>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Current Status</label>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ \App\Models\User::patientStatusLabel($selectedPatientCurrentStatus) }}</p>
                        </div>
                        <div>
                            <label for="pendingStatus" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">New Status</label>
                            <select id="pendingStatus" wire:model="pendingStatus" class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach ($patientStatuses as $allowedStatus)
                                    <option value="{{ $allowedStatus }}">{{ \App\Models\User::patientStatusLabel($allowedStatus) }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('pendingStatus')" />
                        </div>
                    </div>

                    <div>
                        <label for="doctorApprovalNote" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Doctor Note</label>
                        <textarea
                            id="doctorApprovalNote"
                            wire:model="doctorApprovalNote"
                            rows="4"
                            class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Provide a clinical or operational reason for this status update."
                        ></textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('doctorApprovalNote')" />
                        <x-input-error class="mt-2" :messages="$errors->get('selectedPatientId')" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeStatusUpdateModal" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Status</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
