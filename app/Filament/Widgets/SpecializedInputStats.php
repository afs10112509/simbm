<?php

namespace App\Filament\Widgets;

use App\Models\BrilinkDailySnapshot;
use App\Services\BrilinkSnapshotService;
use App\Support\AccessControl;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SpecializedInputStats extends StatsOverviewWidget
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
            ? "Ringkasan Brilink — {$branchName}"
            : 'Ringkasan Brilink';
    }

    protected function getStats(): array
    {
        $branchId = AccessControl::userBranchId();

        if ($branchId === null) {
            return [];
        }

        $latest = BrilinkSnapshotService::latestSnapshot($branchId);
        $monthlyUntung = BrilinkSnapshotService::monthlyProfit(
            $branchId,
            now()->month,
            now()->year
        );

        $todaySnapshot = BrilinkDailySnapshot::query()
            ->where('branch_id', $branchId)
            ->whereDate('snapshot_date', today())
            ->first();

        $todayUntung = $todaySnapshot
            ? BrilinkSnapshotService::profitForSnapshot($todaySnapshot)
            : null;

        $daysSinceLastInput = $latest && ! $todaySnapshot
            ? $latest->snapshot_date->diffInDays(today())
            : null;

        $todayDescription = match (true) {
            $todaySnapshot !== null => 'Sudah diinput hari ini',
            $daysSinceLastInput === null => 'Belum ada riwayat input',
            $daysSinceLastInput === 0 => 'Belum diinput hari ini',
            default => "Terakhir input {$daysSinceLastInput} hari lalu",
        };

        return [
            Stat::make(
                'Saldo Terakhir',
                $latest
                    ? 'Rp ' . number_format((float) $latest->total_balance, 0, ',', '.')
                    : '-'
            )
                ->description($latest
                    ? $latest->snapshot_date->translatedFormat('d M Y')
                    : 'Belum ada input saldo')
                ->color('primary'),

            Stat::make(
                'Untung Bulan Ini',
                'Rp ' . number_format($monthlyUntung, 0, ',', '.')
            )
                ->description(now()->translatedFormat('F Y'))
                ->color('success'),

            Stat::make(
                'Untung Hari Ini',
                $todayUntung !== null
                    ? 'Rp ' . number_format($todayUntung, 0, ',', '.')
                    : '-'
            )
                ->description($todayDescription)
                ->color($todaySnapshot ? 'success' : ($daysSinceLastInput > 1 ? 'danger' : 'warning')),
        ];
    }
}
