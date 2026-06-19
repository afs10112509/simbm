<?php

namespace App\Models;

use App\Support\AccountPurpose;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'type',
        'purpose',
        'bank_name',
        'account_number',
        'balance',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function brilinkSnapshotLines()
    {
        return $this->hasMany(BrilinkDailySnapshotLine::class);
    }

    public function scopeForPurpose($query, string $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    public function scopeForPic($query)
    {
        return $query->forPurpose(AccountPurpose::GENERAL);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function hasBalanceHistory(): bool
    {
        if ($this->transactions()->exists()) {
            return true;
        }

        if ($this->brilinkSnapshotLines()->exists()) {
            return true;
        }

        return Transfer::query()
            ->where(function ($query) {
                $query->where('from_account_id', $this->id)
                    ->orWhere('to_account_id', $this->id);
            })
            ->exists();
    }
}
