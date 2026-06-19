<?php

namespace App\Filament\Widgets;

use App\Models\ServiceRecord;
use App\Models\ServiceTechnician;
use App\Support\AccessControl;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServiceStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return AccessControl::isKonterPic();
    }

    protected function getHeading(): ?string
    {
        $branchName = auth()->user()->branch?->name;

        return $branchName
            ? "Ringkasan Service — {$branchName}"
            : 'Ringkasan Service';
    }

    protected function getStats(): array
    {
        $branchId = auth()->user()->branch_id;

        $baseQuery = ServiceRecord::query()->where('branch_id', $branchId);

        $monthlyProfit = (clone $baseQuery)
            ->whereMonth('service_date', now()->month)
            ->whereYear('service_date', now()->year)
            ->sum('profit');

        $monthlyPrice = (clone $baseQuery)
            ->whereMonth('service_date', now()->month)
            ->whereYear('service_date', now()->year)
            ->sum('price');

        $technicianCount = ServiceTechnician::query()
            ->forBranch($branchId)
            ->active()
            ->count();

        return [
            Stat::make(
                'Omzet Bulan Ini',
                'Rp ' . number_format((float) $monthlyPrice, 0, ',', '.')
            )
                ->description(now()->translatedFormat('F Y'))
                ->color('primary'),

            Stat::make(
                'Laba Bulan Ini',
                'Rp ' . number_format((float) $monthlyProfit, 0, ',', '.')
            )
                ->description('Bagi 2: Rp ' . number_format($monthlyProfit / 2, 0, ',', '.'))
                ->color('success'),

            Stat::make('Tukang Aktif', (string) $technicianCount)
                ->description('Siap input service')
                ->color('warning'),
        ];
    }
}
