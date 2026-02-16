<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patient Status Audit Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
        h1 { margin-bottom: 6px; font-size: 22px; }
        p.meta { margin-top: 0; margin-bottom: 16px; color: #4b5563; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; vertical-align: top; text-align: left; }
        th { background: #f3f4f6; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Patient Status Audit Report</h1>
    <p class="meta">Generated at: {{ now()->format('Y-m-d H:i:s') }} | Records: {{ $decisions->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>Patient</th>
                <th>Status Change</th>
                <th>Decision</th>
                <th>Admin</th>
                <th>Doctor</th>
                <th class="nowrap">Decided At</th>
                <th>Admin Request Note</th>
                <th>Doctor Decision Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($decisions as $decision)
                <tr>
                    <td>
                        <div>{{ $decision->patient?->name ?? 'N/A' }}</div>
                        <div>{{ $decision->patient?->email ?? 'N/A' }}</div>
                    </td>
                    <td>{{ \Illuminate\Support\Str::headline($decision->current_status) }} -> {{ \Illuminate\Support\Str::headline($decision->requested_status) }}</td>
                    <td>{{ \Illuminate\Support\Str::headline($decision->status) }}</td>
                    <td>{{ $decision->admin?->name ?? 'N/A' }}</td>
                    <td>{{ $decision->doctor?->doctorUser?->name ?? 'N/A' }}</td>
                    <td class="nowrap">{{ $decision->decided_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                    <td>{{ $decision->admin_request_note }}</td>
                    <td>{{ $decision->doctor_decision_note }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No audit records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
