<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Upah Kerja</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; margin: 0; padding: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .meta { color: #6b7280; margin: 4px 0 14px; }
        .total-box { border: 1px solid #d1d5db; padding: 10px; margin-bottom: 14px; font-size: 14px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; }
        th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="title">Laporan Upah Kerja</div>
    <div class="meta">{{ $appName }} · Dicetak {{ $generatedAt }}</div>
    @if ($filterSummary !== [])
        <div class="meta">
            @foreach ($filterSummary as $label => $value)
                <span><strong>{{ $label }}:</strong> {{ $value }} · </span>
            @endforeach
        </div>
    @endif

    <div class="total-box">Total Upah Kerja: Rp {{ number_format($totalUpah, 0, ',', '.') }}</div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pekerja</th>
                <th>Jasa</th>
                <th class="right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
                <tr>
                    <td>{{ \App\Support\RecordDateTime::forTransaction($record) }}</td>
                    <td>{{ $record->worker?->name ?? '-' }}</td>
                    <td>{{ $record->service_type ? \App\Support\UpahKerjaServices::label($record->service_type) : '-' }}</td>
                    <td class="right">Rp {{ number_format((float) $record->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
