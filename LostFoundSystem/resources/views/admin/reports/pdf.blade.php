<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 10px; }
        h1 { color: #123a93; font-size: 22px; margin: 0 0 4px; }
        h2 { color: #123a93; font-size: 13px; margin: 22px 0 8px; }
        .meta { color: #64748b; margin: 0; }
        .summary { width: 100%; border-collapse: separate; border-spacing: 8px; margin: 16px -8px 8px; }
        .summary td { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px; width: 16.66%; }
        .summary strong { display: block; color: #123a93; font-size: 17px; margin-bottom: 3px; }
        .summary span { color: #475569; }
        table.data { width: 100%; border-collapse: collapse; }
        .data th { background: #123a93; color: #fff; padding: 7px; text-align: left; font-size: 9px; }
        .data td { border-bottom: 1px solid #dbe4f0; padding: 7px; vertical-align: top; }
        .data tr:nth-child(even) td { background: #f8fafc; }
        .status { font-weight: bold; }
        .footer { position: fixed; bottom: -12px; left: 0; right: 0; color: #64748b; font-size: 8px; text-align: center; }
    </style>
</head>
<body>
    <h1>Lost &amp; Found Administrative Report</h1>
    <p class="meta">Generated {{ $report['generatedAt'] }} by {{ $userData['name'] }}</p>

    <table class="summary">
        <tr>
            @foreach($report['summary'] as $label => $value)
                <td><strong>{{ $value }}</strong><span>{{ $label }}</span></td>
            @endforeach
        </tr>
    </table>

    <h2>Claims</h2>
    <table class="data">
        <thead><tr><th>Claim</th><th>Status</th><th>Claimant</th><th>Found item</th><th>Submitted</th></tr></thead>
        <tbody>
            @forelse($report['claims'] as $claim)
                <tr><td>CLM-{{ $claim->id }}</td><td class="status">{{ ucfirst($claim->status) }}</td><td>{{ $claim->user?->name ?? 'User #'.$claim->user_id }}</td><td>{{ $claim->foundItem?->item_name ?? 'Item #'.$claim->found_item_id }}</td><td>{{ $claim->created_at }}</td></tr>
            @empty
                <tr><td colspan="5">No claims have been submitted.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Lost Items</h2>
    <table class="data">
        <thead><tr><th>Item</th><th>Location</th><th>Date lost</th><th>Status</th><th>Reported by</th></tr></thead>
        <tbody>
            @forelse($report['lostItems'] as $item)
                <tr><td>{{ $item->item_name }}</td><td>{{ $item->location_lost }}</td><td>{{ $item->date_lost }}</td><td class="status">{{ ucfirst($item->status) }}</td><td>{{ $item->user?->name ?? 'User #'.$item->user_id }}</td></tr>
            @empty
                <tr><td colspan="5">No lost items have been reported.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Found Items</h2>
    <table class="data">
        <thead><tr><th>Item</th><th>Location</th><th>Date found</th><th>Status</th><th>Reported by</th></tr></thead>
        <tbody>
            @forelse($report['foundItems'] as $item)
                <tr><td>{{ $item->item_name }}</td><td>{{ $item->location_found }}</td><td>{{ $item->date_found }}</td><td class="status">{{ ucfirst($item->status) }}</td><td>{{ $item->user?->name ?? 'User #'.$item->user_id }}</td></tr>
            @empty
                <tr><td colspan="5">No found items have been reported.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Lost &amp; Found Tracking System</div>
</body>
</html>
