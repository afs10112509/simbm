<?php

namespace App\Filament\Widgets;

use App\Support\AccountBalanceSummary;
use App\Support\AccountPurpose;
use App\Support\AccessControl;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountBalanceStats extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return AccessControl::isPic() && auth()->user()->branch_id;
    }

    protected function getHeading(): ?string
    {
        $branchName = auth()->user()->branch?->name;

        return $branchName
            ? "Saldo Akun Umum — {$branchName}"
            : 'Saldo Akun Umum (PIC)';
    }

    protected function getStats(): array
    {
        $accounts = AccountBalanceSummary::accountsForPurpose(
            auth()->user()->branch_id,
            AccountPurpose::GENERAL
        );

        if ($accounts->isEmpty()) {
            return [
                Stat::make('Belum ada akun umum', '-')
                    ->description('Hubungi pemilik — pisahkan dari akun Brilink/Service/Upah Kerja')
                    ->color('gray'),
            ];
        }

        return $accounts->map(function ($account) {
            $typeLabel = match ($account->type) {
                'cash' => 'Tunai',
                'bank' => 'Bank',
                'ewallet' => 'Dompet Digital',
                default => $account->type,
            };

            return Stat::make(
                $account->name,
                'Rp ' . number_format((float) $account->balance, 0, ',', '.')
            )
                ->description($typeLabel)
                ->color('success');
        })->all();
    }
}
