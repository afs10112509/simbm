<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

class AccountBalanceService
{
    public static function applyTransaction(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $account = $transaction->account;

            if (! $account) {
                return;
            }

            if ($transaction->type === 'income') {
                $account->increment('balance', $transaction->amount);
            } elseif ($transaction->type === 'expense') {
                $account->decrement('balance', $transaction->amount);
            }
        });
    }

    public static function reverseTransaction(
        Account|int|string|null $account,
        string $type,
        string|float $amount,
    ): void {
        DB::transaction(function () use ($account, $type, $amount) {
            $account = self::resolveAccount($account);

            if (! $account) {
                return;
            }

            if ($type === 'income') {
                $account->decrement('balance', $amount);
            } elseif ($type === 'expense') {
                $account->increment('balance', $amount);
            }
        });
    }

    public static function applyTransfer(Transfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $from = $transfer->fromAccount;
            $to = $transfer->toAccount;

            if (! $from || ! $to) {
                return;
            }

            $from->decrement('balance', $transfer->amount);
            $to->increment('balance', $transfer->amount);
        });
    }

    public static function reverseTransfer(
        Account|int|string|null $fromAccount,
        Account|int|string|null $toAccount,
        string|float $amount,
    ): void {
        DB::transaction(function () use ($fromAccount, $toAccount, $amount) {
            $from = self::resolveAccount($fromAccount);
            $to = self::resolveAccount($toAccount);

            if (! $from || ! $to) {
                return;
            }

            $from->increment('balance', $amount);
            $to->decrement('balance', $amount);
        });
    }

    private static function resolveAccount(Account|int|string|null $account): ?Account
    {
        if ($account instanceof Account) {
            return $account;
        }

        if ($account !== null) {
            return Account::find($account);
        }

        return null;
    }
}
