<?php

namespace App\Support;

use App\Models\Account;
use Illuminate\Support\Collection;

class AccountBalanceSummary
{
    public static function totalForPurpose(?int $branchId, string $purpose): float
    {
        if ($branchId === null) {
            return 0.0;
        }

        return (float) Account::query()
            ->forBranch($branchId)
            ->forPurpose($purpose)
            ->active()
            ->sum('balance');
    }

    public static function accountsForPurpose(?int $branchId, string $purpose): Collection
    {
        if ($branchId === null) {
            return collect();
        }

        return Account::query()
            ->forBranch($branchId)
            ->forPurpose($purpose)
            ->active()
            ->orderBy('name')
            ->get();
    }

    public static function formattedTotalForPurpose(?int $branchId, string $purpose): string
    {
        return 'Rp ' . number_format(self::totalForPurpose($branchId, $purpose), 0, ',', '.');
    }
}
