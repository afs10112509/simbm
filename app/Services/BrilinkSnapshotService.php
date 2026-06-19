<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BrilinkDailySnapshot;
use App\Models\BrilinkDailySnapshotLine;
use App\Support\AccountPurpose;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BrilinkSnapshotService
{
    public static function save(int $branchId, int $userId, string $date, array $balances): BrilinkDailySnapshot
    {
        return DB::transaction(function () use ($branchId, $userId, $date, $balances) {
            $validAccountIds = Account::query()
                ->where('branch_id', $branchId)
                ->forPurpose(AccountPurpose::BRILINK)
                ->active()
                ->pluck('id')
                ->all();

            $total = 0.0;
            $normalized = [];

            foreach ($balances as $accountId => $amount) {
                $accountId = (int) $accountId;

                if (! in_array($accountId, $validAccountIds, true)) {
                    continue;
                }

                $balance = (float) ($amount ?? 0);
                $normalized[$accountId] = $balance;
                $total += $balance;
            }

            $snapshot = BrilinkDailySnapshot::query()->updateOrCreate(
                [
                    'branch_id' => $branchId,
                    'snapshot_date' => $date,
                ],
                [
                    'user_id' => $userId,
                    'total_balance' => $total,
                ]
            );

            $snapshot->lines()->delete();

            foreach ($normalized as $accountId => $balance) {
                BrilinkDailySnapshotLine::create([
                    'brilink_daily_snapshot_id' => $snapshot->id,
                    'account_id' => $accountId,
                    'balance' => $balance,
                ]);

                Account::query()
                    ->whereKey($accountId)
                    ->where('branch_id', $branchId)
                    ->update(['balance' => $balance]);
            }

            return $snapshot->load('lines.account');
        });
    }

    public static function previousTotal(int $branchId, Carbon|string $date): float
    {
        $dateString = $date instanceof Carbon
            ? $date->toDateString()
            : Carbon::parse($date)->toDateString();

        return (float) BrilinkDailySnapshot::query()
            ->where('branch_id', $branchId)
            ->where('snapshot_date', '<', $dateString)
            ->orderByDesc('snapshot_date')
            ->value('total_balance') ?? 0;
    }

    public static function profitForSnapshot(BrilinkDailySnapshot $snapshot): float
    {
        return (float) $snapshot->total_balance
            - self::previousTotal($snapshot->branch_id, $snapshot->snapshot_date);
    }

    public static function daysSincePreviousSnapshot(int $branchId, Carbon|string $date): ?int
    {
        $dateCarbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $previousDate = BrilinkDailySnapshot::query()
            ->where('branch_id', $branchId)
            ->where('snapshot_date', '<', $dateCarbon->toDateString())
            ->orderByDesc('snapshot_date')
            ->value('snapshot_date');

        if ($previousDate === null) {
            return null;
        }

        return Carbon::parse($previousDate)->diffInDays($dateCarbon);
    }

    public static function missedInputDays(int $branchId, Carbon|string $date): ?int
    {
        $days = self::daysSincePreviousSnapshot($branchId, $date);

        if ($days === null) {
            return null;
        }

        return $days > 1 ? $days - 1 : 0;
    }

    public static function reportRows(int $branchId, Carbon $start, Carbon $end): Collection
    {
        return BrilinkDailySnapshot::query()
            ->where('branch_id', $branchId)
            ->whereBetween('snapshot_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('snapshot_date')
            ->get()
            ->map(function (BrilinkDailySnapshot $snapshot) use ($branchId) {
                $missedDays = self::missedInputDays($branchId, $snapshot->snapshot_date);

                return [
                    'snapshot' => $snapshot,
                    'kemarin' => self::previousTotal($branchId, $snapshot->snapshot_date),
                    'saldo' => (float) $snapshot->total_balance,
                    'untung' => self::profitForSnapshot($snapshot),
                    'missedDays' => $missedDays,
                    'hasGapWarning' => ($missedDays ?? 0) > 0,
                ];
            });
    }

    public static function monthlyProfit(int $branchId, int $month, int $year): float
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return (float) self::reportRows($branchId, $start, $end)->sum('untung');
    }

    public static function monthlyProfitAllBranches(int $month, int $year): float
    {
        return (float) BrilinkDailySnapshot::query()
            ->whereMonth('snapshot_date', $month)
            ->whereYear('snapshot_date', $year)
            ->get()
            ->sum(fn (BrilinkDailySnapshot $snapshot) => self::profitForSnapshot($snapshot));
    }

    public static function latestSnapshot(int $branchId): ?BrilinkDailySnapshot
    {
        return BrilinkDailySnapshot::query()
            ->where('branch_id', $branchId)
            ->orderByDesc('snapshot_date')
            ->first();
    }

    public static function balancesForDate(int $branchId, string $date): array
    {
        $snapshot = BrilinkDailySnapshot::query()
            ->where('branch_id', $branchId)
            ->whereDate('snapshot_date', $date)
            ->with('lines')
            ->first();

        if (! $snapshot) {
            return [];
        }

        return $snapshot->lines
            ->mapWithKeys(fn (BrilinkDailySnapshotLine $line) => [
                $line->account_id => number_format((float) $line->balance, 0, ',', '.'),
            ])
            ->all();
    }
}
