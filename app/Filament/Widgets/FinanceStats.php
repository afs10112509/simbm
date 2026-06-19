<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Support\AccessControl;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceStats extends BaseWidget
{
    public static function canView(): bool
    {
        return AccessControl::canViewDashboardWidgets();
    }

    protected function getStats(): array
    {
        $query = Transaction::query()->forLedgerReport();

        if (! AccessControl::canViewAllBranches()) {
            $branchId = AccessControl::userBranchId();

            if ($branchId !== null) {
                $query->where('branch_id', $branchId);
            }
        }

        $monthlyIncome = (clone $query)
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $monthlyExpense = (clone $query)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $monthlyProfit = $monthlyIncome - $monthlyExpense;

        return [
            Stat::make(
                'Pemasukan Bulan Ini',
                'Rp ' . number_format((float) $monthlyIncome, 0, ',', '.')
            )
                ->description('Kas PIC & Service · Brilink lihat Laporan Brilink')
                ->color('primary'),

            Stat::make(
                'Pengeluaran Bulan Ini',
                'Rp ' . number_format((float) $monthlyExpense, 0, ',', '.')
            )
                ->description('Tidak termasuk upah kerja')
                ->color('danger'),

            Stat::make(
                'Laba Bulan Ini',
                'Rp ' . number_format((float) $monthlyProfit, 0, ',', '.')
            )
                ->description('Dari transaksi kas & service')
                ->color($monthlyProfit >= 0 ? 'success' : 'danger'),
        ];
    }
}
