<?php

namespace App\Support;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Validation\ValidationException;

class AccountBalanceValidator
{
    public static function formatIdr(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public static function assertSufficientBalance(
        Account|int $account,
        float $amount,
        string $field = 'amount',
    ): void {
        if ($amount <= 0) {
            return;
        }

        $account = self::resolveAccount($account);
        $available = (float) $account->balance;

        if ($available < $amount) {
            throw ValidationException::withMessages([
                $field => self::insufficientMessage($available),
            ]);
        }
    }

    public static function assertTransferAllowed(
        ?Transfer $existing,
        int $fromAccountId,
        float $amount,
    ): void {
        if ($amount <= 0) {
            return;
        }

        $from = self::resolveAccount($fromAccountId);
        $available = (float) $from->balance;

        if ($existing && (int) $existing->from_account_id === $fromAccountId) {
            $available += (float) $existing->amount;
        }

        if ($available < $amount) {
            throw ValidationException::withMessages([
                'amount' => self::insufficientMessage($available),
            ]);
        }
    }

    public static function assertBatchTransactions(int $accountId, array $items): void
    {
        $account = self::resolveAccount($accountId);
        $balance = (float) $account->balance;

        foreach ($items as $index => $item) {
            $amount = (float) (NominalInput::parse($item['amount'] ?? null) ?? 0);

            if ($amount <= 0) {
                continue;
            }

            if (($item['type'] ?? null) === 'income') {
                $balance += $amount;

                continue;
            }

            if (($item['type'] ?? null) === 'expense') {
                $balance -= $amount;

                if ($balance < 0) {
                    throw ValidationException::withMessages([
                        "transactions.{$index}.amount" => self::insufficientMessage((float) $account->balance),
                    ]);
                }
            }
        }
    }

    public static function assertTransactionUpdateAllowed(
        Transaction $existing,
        int $accountId,
        string $newType,
        float $newAmount,
    ): void {
        if ($newType !== 'expense' || $newAmount <= 0) {
            return;
        }

        $projected = self::projectedBalanceAfterTransactionUpdate(
            $existing,
            $accountId,
            $newType,
            $newAmount,
        );

        if ($projected < 0) {
            throw ValidationException::withMessages([
                'amount' => self::insufficientMessage($projected + $newAmount),
            ]);
        }
    }

    public static function assertServiceModalAllowed(Account $account, float $price, float $modal): void
    {
        if ($modal <= 0) {
            return;
        }

        $balance = (float) $account->balance;

        if ($price > 0) {
            $balance += $price;
        }

        if ($balance < $modal) {
            throw new \RuntimeException(
                'Saldo akun service tidak mencukupi untuk modal. Tersedia: '
                . self::formatIdr($balance)
                . ', modal: '
                . self::formatIdr($modal)
                . '.'
            );
        }
    }

    public static function projectedBalanceAfterTransactionUpdate(
        Transaction $existing,
        int $accountId,
        string $newType,
        float $newAmount,
    ): float {
        $account = self::resolveAccount($accountId);
        $balance = (float) $account->balance;

        if ((int) $existing->account_id === $accountId) {
            if ($existing->type === 'income') {
                $balance -= (float) $existing->amount;
            } elseif ($existing->type === 'expense') {
                $balance += (float) $existing->amount;
            }
        }

        if ($newType === 'income') {
            $balance += $newAmount;
        } elseif ($newType === 'expense') {
            $balance -= $newAmount;
        }

        return $balance;
    }

    private static function insufficientMessage(float $available): string
    {
        return 'Saldo tidak mencukupi. Tersedia: ' . self::formatIdr(max($available, 0)) . '.';
    }

    private static function resolveAccount(Account|int $account): Account
    {
        if ($account instanceof Account) {
            return $account;
        }

        return Account::query()->findOrFail($account);
    }
}
