<div class="mx-auto max-w-[85rem] px-4 py-5 sm:px-6 lg:px-8 lg:py-7">
    <x-success-toast :livewire-only="true" />

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="grid gap-3 border-b border-gray-200 px-6 py-4 md:flex md:items-center md:justify-between dark:border-neutral-700">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-100">Patient Status Requests</h3>
                <p class="text-sm text-gray-500 dark:text-neutral-400">Review and decide admin-submitted status change requests for your patients.</p>
            </div>
            <div class="grid w-full gap-2 sm:grid-cols-2 md:w-auto">
                <select wire:model.live="statusFilter" class="block w-full rounded-lg border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                    @foreach ($requestStatuses as $requestStatus)
                        <option value="{{ $requestStatus }}">{{ \Illuminate\Support\Str::headline($requestStatus) }}</option>
                    @endforeach
                </select>
                <select wire:model.live="perPage" class="block w-full rounded-lg border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
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
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Requested By</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Status Change</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Request Note</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Decision</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                    @forelse ($requests as $request)
                        @php
                            $statusMap = [
                                'pending' => ['label' => 'Pending', 'classes' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-500'],
                                'approved' => ['label' => 'Approved', 'classes' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-500'],
                                'rejected' => ['label' => 'Rejected', 'classes' => 'bg-rose-100 text-rose-800 dark:bg-rose-500/10 dark:text-rose-500'],
                            ];
                            $statusMeta = $statusMap[$request->status] ?? $statusMap['pending'];
                        @endphp
                        <tr wire:key="doctor-status-request-{{ $request->id }}" class="align-top">
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $request->patient?->name ?? 'N/A' }}</p>
                                <p>{{ $request->patient?->email ?? 'N/A' }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                <p>{{ $request->admin?->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500 dark:text-neutral-500">{{ $request->created_at?->format('Y-m-d H:i') }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                <p>{{ \App\Models\User::patientStatusLabel($request->current_status) }} -> {{ \App\Models\User::patientStatusLabel($request->requested_status) }}</p>
                                <span class="mt-2 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusMeta['classes'] }}">{{ $statusMeta['label'] }}</span>
                            </td>
                            <td class="max-w-xs px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">{{ \Illuminate\Support\Str::limit($request->admin_request_note, 120) }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                @if ($request->status === \App\Models\PatientStatusChangeRequest::STATUS_PENDING)
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            wire:click="openDecisionModal({{ $request->id }}, '{{ \App\Models\PatientStatusChangeRequest::STATUS_APPROVED }}')"
                                            class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                                        >
                                            Approve
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="openDecisionModal({{ $request->id }}, '{{ \App\Models\PatientStatusChangeRequest::STATUS_REJECTED }}')"
                                            class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                @else
                                    <p class="text-xs text-gray-500 dark:text-neutral-500">Decided: {{ $request->decided_at?->format('Y-m-d H:i') ?? 'N/A' }}</p>
                                    @if ($request->doctor_decision_note)
                                        <p class="mt-1 text-xs text-gray-700 dark:text-neutral-300">{{ \Illuminate\Support\Str::limit($request->doctor_decision_note, 120) }}</p>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-neutral-400">No status requests found for this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-6 py-4 dark:border-neutral-700">{{ $requests->links() }}</div>
    </div>

    @if ($showDecisionModal)
        <div wire:transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/50" wire:click="closeDecisionModal"></div>
            <div class="relative z-10 w-full max-w-xl rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <form wire:submit="confirmDecision" class="space-y-4 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">{{ $selectedDecision === \App\Models\PatientStatusChangeRequest::STATUS_APPROVED ? 'Approve Request' : 'Reject Request' }}</h3>
                            <p class="text-xs text-slate-500">This decision is final and logged in the audit trail.</p>
                        </div>
                        <button type="button" wire:click="closeDecisionModal" class="text-slate-400 hover:text-slate-600">x</button>
                    </div>

                    <div>
                        <label for="doctorDecisionNote" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Decision Note</label>
                        <textarea
                            id="doctorDecisionNote"
                            wire:model="doctorDecisionNote"
                            rows="4"
                            class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Provide the clinical/operational reason for this decision."
                        ></textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('doctorDecisionNote')" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeDecisionModal" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Confirm Decision</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
