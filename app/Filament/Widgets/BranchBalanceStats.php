<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Support\AccessControl;
use App\Support\AccountBalanceSummary;
use App\Support\AccountPurpose;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BranchBalanceStats extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected ?string $heading = 'Saldo Kas Umum Per Cabang';

    public static function canView(): bool
    {
        return AccessControl::canViewOwnerDashboardWidgets();
    }

    protected function getStats(): array
    {
        return Branch::query()
            ->orderBy('name')
            ->get()
            ->map(function (Branch $branch) {
                $balance = AccountBalanceSummary::totalForPurpose(
                    $branch->id,
                    AccountPurpose::GENERAL
                );

                return Stat::make(
                    $branch->name,
                    'Rp ' . number_format($balance, 0, ',', '.')
                )
                    ->description('Kas harian PIC')
                    ->color($balance >= 0 ? 'success' : 'danger');
            })
            ->all();
    }
}
