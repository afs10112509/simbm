<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Support\AccessControl;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class CashflowChart extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Grafik Arus Kas Bulanan (Kas PIC & Service)';

    public static function canView(): bool
    {
        return AccessControl::canViewOwnerDashboardWidgets();
    }

    protected function getData(): array
    {
        $months = collect(range(1, 12))->map(function ($month) {
            return Carbon::create(now()->year, $month, 1)->translatedFormat('M');
        });

        $incomeData = [];
        $expenseData = [];

        foreach (range(1, 12) as $month) {

            $incomeData[] = Transaction::forLedgerReport()
                ->where('type', 'income')
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount');

            $expenseData[] = Transaction::forLedgerReport()
                ->where('type', 'expense')
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomeData,
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseData,
                ],
            ],

            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
