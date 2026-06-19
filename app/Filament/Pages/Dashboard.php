<?php

namespace App\Filament\Pages;

use App\Support\AccessControl;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        if (AccessControl::isPic()) {
            return 'Beranda';
        }

        return 'Dashboard';
    }

    public static function getNavigationLabel(): string
    {
        if (AccessControl::isPic()) {
            return 'Beranda';
        }

        return 'Dashboard';
    }

    public function getWidgets(): array
    {
        if (AccessControl::isPic()) {
            return [
                \App\Filament\Widgets\PicDailyChecklistWidget::class,
                \App\Filament\Widgets\FinanceStats::class,
                \App\Filament\Widgets\SpecializedInputStats::class,
                \App\Filament\Widgets\ServiceStats::class,
                \App\Filament\Widgets\UpahKerjaStats::class,
                \App\Filament\Widgets\RecentTransactions::class,
                \App\Filament\Widgets\UpahKerjaRecent::class,
                \App\Filament\Widgets\SpecializedRecentTransactions::class,
            ];
        }

        return [
            \App\Filament\Widgets\OwnerBusinessOverview::class,
            \App\Filament\Widgets\OwnerBranchSummaryWidget::class,
            \App\Filament\Widgets\FinanceStats::class,
            \App\Filament\Widgets\AccountBalanceStats::class,
            \App\Filament\Widgets\BranchBalanceStats::class,
            \App\Filament\Widgets\CashflowChart::class,
            \App\Filament\Widgets\RecentTransactions::class,
        ];
    }
}
