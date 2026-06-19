<?php

namespace App\Models;

use App\Services\AccountBalanceService;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Transfer extends Model
{
    use LogsActivity;

    protected $fillable = [
        'branch_id',
        'from_account_id',
        'to_account_id',
        'user_id',
        'amount',
        'description',
        'transfer_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'branch_id',
                'from_account_id',
                'to_account_id',
                'amount',
                'description',
                'transfer_date',
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(
                fn (string $eventName) =>
                    "Transfer {$eventName}"
            );
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::creating(function ($transfer) {
            if (auth()->check()) {
                $transfer->user_id = auth()->id();
            }
        });

        static::created(function ($transfer) {
            AccountBalanceService::applyTransfer($transfer);
        });

        static::updating(function ($transfer) {
            AccountBalanceService::reverseTransfer(
                $transfer->getOriginal('from_account_id'),
                $transfer->getOriginal('to_account_id'),
                $transfer->getOriginal('amount'),
            );
        });

        static::updated(function ($transfer) {
            AccountBalanceService::applyTransfer($transfer);
        });

        static::deleted(function ($transfer) {
            AccountBalanceService::reverseTransfer(
                $transfer->from_account_id,
                $transfer->to_account_id,
                $transfer->amount,
            );
        });
    }
}
