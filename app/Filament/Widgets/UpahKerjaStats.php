<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\Worker;
use App\Support\AccessControl;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UpahKerjaStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return AccessControl::isBengkelPic();
    }

    protected function getHeading(): ?string
    {
        $branchName = auth()->user()->branch?->name;

        return $branchName
            ? "Ringkasan Upah Kerja — {$branchName}"
            : 'Ringkasan Upah Kerja';
    }

    protected function getStats(): array
    {
        $categoryId = TransactionCategory::findBySlug('upah_kerja')?->id;
        $branchId = auth()->user()->branch_id;

        $baseQuery = Transaction::query()
            ->when($categoryId, fn ($query) => $query->where('transaction_category_id', $categoryId))
            ->where('branch_id', $branchId);

        $monthlyTotal = (clone $baseQuery)
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $todayTotal = (clone $baseQuery)
            ->whereDate('transaction_date', today())
            ->sum('amount');

        $workerCount = Worker::query()
            ->forBranch($branchId)
            ->active()
            ->count();

        return [
            Stat::make(
                'Upah Bulan Ini',
                'Rp ' . number_format((float) $monthlyTotal, 0, ',', '.')
            )
                ->description(now()->translatedFormat('F Y'))
                ->color('primary'),

            Stat::make(
                'Upah Hari Ini',
                'Rp ' . number_format((float) $todayTotal, 0, ',', '.')
            )
                ->description(today()->translatedFormat('d M Y'))
                ->color('success'),

            Stat::make('Pekerja Aktif', (string) $workerCount)
                ->description('Siap diinput upah')
                ->color('warning'),
        ];
    }
}
