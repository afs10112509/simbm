<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Service</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; margin: 0; padding: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .meta { color: #6b7280; margin: 4px 0 14px; }
        .summary td { border: 1px solid #d1d5db; padding: 8px; width: 25%; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 5px 6px; }
        table.data th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="title">Laporan Service</div>
    <div class="meta">{{ $appName }} · Dicetak {{ $generatedAt }}</div>
    @if ($filterSummary !== [])
        <div class="meta">
            @foreach ($filterSummary as $label => $value)
                <span><strong>{{ $label }}:</strong> {{ $value }} · </span>
            @endforeach
        </div>
    @endif

    <table class="summary" style="width:100%; border-collapse:collapse; margin-bottom:14px;">
        <tr>
            <td><strong>Total Harga</strong><br>Rp {{ number_format($totalPrice, 0, ',', '.') }}</td>
            <td><strong>Total Modal</strong><br>Rp {{ number_format($totalModal, 0, ',', '.') }}</td>
            <td><strong>Total Laba</strong><br>Rp {{ number_format($totalProfit, 0, ',', '.') }}</td>
            <td><strong>Bagi 2</strong><br>Rp {{ number_format($splitProfit, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                @if ($showBranch)<th>Cabang</th>@endif
                <th>Tukang</th>
                <th>Perangkat</th>
                <th>Kerusakan</th>
                <th class="right">Modal</th>
                <th class="right">Harga</th>
                <th class="right">Laba</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
                <tr>
                    <td>{{ \App\Support\RecordDateTime::format($record->service_date, $record->created_at) }}</td>
                    @if ($showBranch)<td>{{ $record->branch?->name }}</td>@endif
                    <td>{{ $record->technician?->name ?? '-' }}</td>
                    <td>{{ $record->device_brand }} {{ $record->device_type }}</td>
                    <td>{{ \App\Support\ServiceDamageTypes::label($record->damage_type) }}</td>
                    <td class="right">Rp {{ number_format((float) $record->modal, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format((float) $record->price, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format((float) $record->profit, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
