<?php

namespace App\Support;

use App\Models\Account;
use App\Models\Branch;
use App\Models\ServiceRecord;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Services\BrilinkSnapshotService;
use Illuminate\Support\Collection;

class OwnerBranchSummary
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function rows(?int $month = null, ?int $year = null): Collection
    {
        $month ??= now()->month;
        $year ??= now()->year;

        return Branch::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(fn (Branch $branch) => self::rowForBranch($branch, $month, $year));
    }

    /**
     * @return array<string, mixed>
     */
    public static function rowForBranch(Branch $branch, int $month, int $year): array
    {
        $isKonter = $branch->type === AccessControl::BRANCH_KONTER;

        return [
            'branch' => $branch,
            'type_label' => AccessControl::branchTypeLabels()[$branch->type] ?? $branch->type,
            'kas_saldo' => self::accountBalanceTotal($branch->id, AccountPurpose::GENERAL),
            'kas_laba_bulan' => self::monthlyKasNet($branch->id, $month, $year),
            'brilink_saldo' => $isKonter ? self::accountBalanceTotal($branch->id, AccountPurpose::BRILINK) : null,
            'brilink_untung_bulan' => $isKonter
                ? BrilinkSnapshotService::monthlyProfit($branch->id, $month, $year)
                : null,
            'service_saldo' => $isKonter ? self::accountBalanceTotal($branch->id, AccountPurpose::SERVICE) : null,
            'service_laba_bulan' => $isKonter ? self::monthlyServiceProfit($branch->id, $month, $year) : null,
            'upah_kerja_bulan' => $branch->type === AccessControl::BRANCH_BENGKEL
                ? self::monthlyUpahKerja($branch->id, $month, $year)
                : null,
        ];
    }

    protected static function accountBalanceTotal(int $branchId, string $purpose): float
    {
        return AccountBalanceSummary::totalForPurpose($branchId, $purpose);
    }

    protected static function monthlyKasNet(int $branchId, int $month, int $year): float
    {
        $base = Transaction::query()
            ->forLedgerReport()
            ->where('branch_id', $branchId)
            ->whereHas('account', fn ($query) => $query->forPurpose(AccountPurpose::GENERAL))
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        $income = (float) (clone $base)->where('type', 'income')->sum('amount');
        $expense = (float) (clone $base)->where('type', 'expense')->sum('amount');

        return $income - $expense;
    }

    protected static function monthlyServiceProfit(int $branchId, int $month, int $year): float
    {
        return (float) ServiceRecord::query()
            ->where('branch_id', $branchId)
            ->whereMonth('service_date', $month)
            ->whereYear('service_date', $year)
            ->sum('profit');
    }

    protected static function monthlyUpahKerja(int $branchId, int $month, int $year): float
    {
        $categoryId = TransactionCategory::findBySlug('upah_kerja')?->id;

        if (! $categoryId) {
            return 0;
        }

        return (float) Transaction::query()
            ->where('branch_id', $branchId)
            ->where('transaction_category_id', $categoryId)
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('amount');
    }
}
