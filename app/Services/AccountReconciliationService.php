<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountReconciliation;
use App\Support\AccessControl;

class AccountReconciliationService
{
    public static function record(
        Account $account,
        float $physicalBalance,
        ?string $notes = null,
    ): AccountReconciliation {
        abort_unless(AccessControl::isOwner(), 403);

        $systemBalance = (float) $account->balance;
        $difference = $physicalBalance - $systemBalance;

        $reconciliation = AccountReconciliation::query()->create([
            'account_id' => $account->id,
            'user_id' => auth()->id(),
            'physical_balance' => $physicalBalance,
            'system_balance' => $systemBalance,
            'difference' => $difference,
            'notes' => $notes,
            'reconciled_at' => now(),
        ]);

        if (abs($difference) >= 0.01) {
            $account->update(['balance' => $physicalBalance]);
        }

        return $reconciliation;
    }
}
