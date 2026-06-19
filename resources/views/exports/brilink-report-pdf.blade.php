<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Brilink</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; margin: 0; padding: 20px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .meta { color: #6b7280; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; }
        th { background: #f3f4f6; text-transform: uppercase; font-size: 9px; }
        .right { text-align: right; }
        .section-title { font-size: 12px; font-weight: bold; margin: 12px 0 6px; }
        .total { font-weight: bold; color: #059669; }
    </style>
</head>
<body>
    <div class="title">Laporan Brilink</div>
    <div class="meta">{{ $appName }} · {{ $periodLabel }} · Dicetak {{ $generatedAt }}</div>
    <div class="meta"><strong>Total Untung:</strong> Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>

    @foreach ($sections as $section)
        <div class="section-title">{{ $section['branch']->name }}</div>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th class="right">Kemarin</th>
                    <th class="right">Saldo</th>
                    <th class="right">Untung</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($section['rows'] as $row)
                    <tr>
                        <td>{{ $row['snapshot']->snapshot_date->format('d/m/y') }}</td>
                        <td class="right">Rp {{ number_format($row['kemarin'], 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($row['saldo'], 0, ',', '.') }}</td>
                        <td class="right total">Rp {{ number_format($row['untung'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="right"><strong>Total</strong></td>
                    <td class="right total">Rp {{ number_format($section['totalUntung'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @endforeach
</body>
</html>
