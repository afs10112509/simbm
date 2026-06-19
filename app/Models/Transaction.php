<?php

namespace App\Models;

use App\Services\AccountBalanceService;
use App\Support\AccountPurpose;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Transaction extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'branch_id',
                'account_id',
                'transaction_category_id',
                'worker_id',
                'service_type',
                'type',
                'amount',
                'description',
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(
                fn (string $eventName) =>
                    "Transaksi {$eventName}"
            );
    }

    protected $fillable = [
        'branch_id',
        'account_id',
        'user_id',
        'type',
        'transaction_category_id',
        'worker_id',
        'service_type',
        'amount',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(
            TransactionCategory::class,
            'transaction_category_id'
        );
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function scopeTrackedInFinance($query)
    {
        return $query->whereNotNull('account_id');
    }

    public function scopeForLedgerReport($query)
    {
        return $query->trackedInFinance()
            ->whereHas(
                'account',
                fn ($accountQuery) => $accountQuery->where('purpose', '!=', AccountPurpose::BRILINK)
            );
    }

    public function isLinkedToServiceRecord(): bool
    {
        return ServiceRecord::query()
            ->where('income_transaction_id', $this->id)
            ->orWhere('expense_transaction_id', $this->id)
            ->exists();
    }

    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (auth()->check()) {
                $transaction->user_id = auth()->id();
            }
        });

        static::created(function ($transaction) {
            if ($transaction->account_id) {
                AccountBalanceService::applyTransaction($transaction);
            }
        });

        static::updating(function ($transaction) {
            if ($transaction->getOriginal('account_id')) {
                AccountBalanceService::reverseTransaction(
                    $transaction->getOriginal('account_id'),
                    $transaction->getOriginal('type'),
                    $transaction->getOriginal('amount'),
                );
            }
        });

        static::updated(function ($transaction) {
            if ($transaction->account_id) {
                AccountBalanceService::applyTransaction($transaction);
            }
        });

        static::deleted(function ($transaction) {
            if ($transaction->account_id) {
                AccountBalanceService::reverseTransaction(
                    $transaction->account_id,
                    $transaction->type,
                    $transaction->amount,
                );
            }
        });
    }
}
