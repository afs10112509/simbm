<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 0;
            padding: 24px;
        }
        .header {
            border-bottom: 2px solid #d97706;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 4px;
        }
        .subtitle {
            font-size: 11px;
            color: #6b7280;
            margin: 0;
        }
        .meta {
            margin-top: 10px;
            font-size: 9px;
            color: #374151;
        }
        .meta span {
            display: inline-block;
            margin-right: 14px;
            margin-bottom: 4px;
        }
        .summary {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
        }
        .summary td {
            width: 33.33%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .summary-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            margin-top: 4px;
        }
        .income { color: #059669; }
        .expense { color: #dc2626; }
        .profit { color: #d97706; }
        table.data {
            width: 100%;
            border-collapse: collapse;
        }
        table.data th,
        table.data td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        table.data th {
            background: #f3f4f6;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        table.data td.amount {
            text-align: right;
            white-space: nowrap;
            font-weight: bold;
        }
        table.data tr:nth-child(even) td {
            background: #fafafa;
        }
        .badge-income { color: #059669; }
        .badge-expense { color: #dc2626; }
        .empty {
            text-align: center;
            padding: 24px;
            color: #6b7280;
            border: 1px dashed #d1d5db;
        }
        .footer {
            margin-top: 16px;
            font-size: 8px;
            color: #9ca3af;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Laporan Keuangan</p>
        <p class="subtitle">{{ $appName }}</p>

        @if ($filterSummary !== [])
            <div class="meta">
                @foreach ($filterSummary as $label => $value)
                    <span><strong>{{ $label }}:</strong> {{ $value }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Total Pemasukan</div>
                <div class="summary-value income">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Total Pengeluaran</div>
                <div class="summary-value expense">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Laba</div>
                <div class="summary-value profit">Rp {{ number_format($laba, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    @if ($transactions->isEmpty())
        <div class="empty">Tidak ada transaksi untuk filter yang dipilih.</div>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 14%">Tanggal</th>
                    @if ($showBranch)
                        <th style="width: 12%">Cabang</th>
                    @endif
                    <th style="width: 12%">Akun</th>
                    <th style="width: 10%">Jenis</th>
                    <th style="width: 14%">Kategori</th>
                    <th style="width: 12%">Nominal</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ \App\Support\RecordDateTime::forTransaction($transaction) }}</td>
                        @if ($showBranch)
                            <td>{{ $transaction->branch?->name ?? '-' }}</td>
                        @endif
                        <td>{{ $transaction->account?->name ?? '-' }}</td>
                        <td class="{{ $transaction->type === 'income' ? 'badge-income' : 'badge-expense' }}">
                            {{ $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                        </td>
                        <td>{{ $transaction->category?->name ?? '-' }}</td>
                        <td class="amount {{ $transaction->type === 'income' ? 'badge-income' : 'badge-expense' }}">
                            Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}
                        </td>
                        <td>{{ $transaction->description ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dicetak: {{ $generatedAt }} · {{ $transactions->count() }} transaksi
    </div>
</body>
</html>
