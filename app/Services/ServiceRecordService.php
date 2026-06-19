<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ServiceRecord;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Support\AccountBalanceValidator;
use App\Support\ServiceDamageTypes;
use Illuminate\Support\Facades\DB;

class ServiceRecordService
{
    public static function create(array $data): ServiceRecord
    {
        return DB::transaction(function () use ($data) {
            $category = TransactionCategory::findBySlug('service');

            if (! $category) {
                throw new \RuntimeException('Kategori service tidak ditemukan.');
            }

            $account = Account::query()
                ->whereKey($data['account_id'])
                ->where('branch_id', $data['branch_id'])
                ->forPurpose(\App\Support\AccountPurpose::SERVICE)
                ->active()
                ->firstOrFail();

            $modal = (float) ($data['modal'] ?? 0);
            $price = (float) $data['price'];
            $profit = ServiceRecord::calculateProfit($price, $modal);

            $account->refresh();
            AccountBalanceValidator::assertServiceModalAllowed($account, $price, $modal);

            $technicianName = $data['technician_name'] ?? '-';
            $damageLabel = ServiceDamageTypes::label($data['damage_type']);
            $description = sprintf(
                '%s %s — %s — %s',
                $data['device_brand'],
                $data['device_type'],
                $damageLabel,
                $technicianName
            );

            $incomeTransactionId = null;
            $expenseTransactionId = null;

            if ($price > 0) {
                $income = Transaction::create([
                    'branch_id' => $data['branch_id'],
                    'account_id' => $account->id,
                    'transaction_category_id' => $category->id,
                    'user_id' => $data['user_id'],
                    'type' => 'income',
                    'amount' => $price,
                    'description' => $description,
                    'transaction_date' => $data['service_date'],
                ]);
                $incomeTransactionId = $income->id;
            }

            if ($modal > 0) {
                $expense = Transaction::create([
                    'branch_id' => $data['branch_id'],
                    'account_id' => $account->id,
                    'transaction_category_id' => $category->id,
                    'user_id' => $data['user_id'],
                    'type' => 'expense',
                    'amount' => $modal,
                    'description' => 'Modal: ' . $description,
                    'transaction_date' => $data['service_date'],
                ]);
                $expenseTransactionId = $expense->id;
            }

            return ServiceRecord::create([
                'branch_id' => $data['branch_id'],
                'service_technician_id' => $data['service_technician_id'],
                'user_id' => $data['user_id'],
                'account_id' => $account->id,
                'income_transaction_id' => $incomeTransactionId,
                'expense_transaction_id' => $expenseTransactionId,
                'service_date' => $data['service_date'],
                'device_brand' => $data['device_brand'],
                'device_type' => $data['device_type'],
                'damage_type' => $data['damage_type'],
                'modal' => $modal,
                'price' => $price,
                'profit' => $profit,
            ]);
        });
    }
}
