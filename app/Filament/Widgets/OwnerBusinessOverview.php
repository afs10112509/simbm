<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\ServiceRecord;
use App\Services\BrilinkSnapshotService;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Support\AccessControl;
use App\Support\AccountPurpose;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OwnerBusinessOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -2;

    protected ?string $heading = 'Ringkasan Bisnis';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return AccessControl::isOwner();
    }

    protected function getStats(): array
    {
        $month = now()->month;
        $year = now()->year;

        $generalBalance = (float) Account::query()
            ->forPurpose(AccountPurpose::GENERAL)
            ->active()
            ->sum('balance');

        $brilinkBalance = (float) Account::query()
            ->forPurpose(AccountPurpose::BRILINK)
            ->active()
            ->sum('balance');

        $serviceBalance = (float) Account::query()
            ->forPurpose(AccountPurpose::SERVICE)
            ->active()
            ->sum('balance');

        $kasMonthly = $this->monthlyNetForPurpose(AccountPurpose::GENERAL, $month, $year);
        $brilinkMonthly = BrilinkSnapshotService::monthlyProfitAllBranches($month, $year);
        $serviceMonthly = (float) ServiceRecord::query()
            ->whereMonth('service_date', $month)
            ->whereYear('service_date', $year)
            ->sum('profit');
        $upahKerjaMonthly = $this->monthlyTotalForCategory('upah_kerja', $month, $year);

        return [
            Stat::make(
                'Kas Harian (PIC)',
                'Rp ' . number_format($generalBalance, 0, ',', '.')
            )
                ->description('Saldo akun umum · Laba bersih bulan ini Rp ' . number_format($kasMonthly, 0, ',', '.'))
                ->color('success'),

            Stat::make(
                'Brilink',
                'Rp ' . number_format($brilinkBalance, 0, ',', '.')
            )
                ->description('Saldo snapshot · Untung bulan ini Rp ' . number_format($brilinkMonthly, 0, ',', '.') . ' (Laporan Brilink)')
                ->color('info'),

            Stat::make(
                'Service',
                'Rp ' . number_format($serviceBalance, 0, ',', '.')
            )
                ->description('Laba bulan ini Rp ' . number_format($serviceMonthly, 0, ',', '.'))
                ->color('warning'),

            Stat::make(
                'Upah Kerja',
                'Rp ' . number_format($upahKerjaMonthly, 0, ',', '.')
            )
                ->description('Total bulan ini (tanpa saldo kas)')
                ->color('danger'),
        ];
    }

    protected function monthlyNetForPurpose(string $purpose, int $month, int $year): float
    {
        $base = Transaction::query()
            ->forLedgerReport()
            ->whereHas('account', fn ($query) => $query->forPurpose($purpose))
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        $income = (float) (clone $base)->where('type', 'income')->sum('amount');
        $expense = (float) (clone $base)->where('type', 'expense')->sum('amount');

        return $income - $expense;
    }

    protected function monthlyTotalForCategory(string $slug, int $month, int $year): float
    {
        $categoryId = TransactionCategory::findBySlug($slug)?->id;

        if (! $categoryId) {
            return 0;
        }

        return (float) Transaction::query()
            ->where('transaction_category_id', $categoryId)
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('amount');
    }
}
