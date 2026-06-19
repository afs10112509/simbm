<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountReconciliation extends Model
{
    protected $fillable = [
        'account_id',
        'user_id',
        'physical_balance',
        'system_balance',
        'difference',
        'notes',
        'reconciled_at',
    ];

    protected $casts = [
        'physical_balance' => 'decimal:2',
        'system_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'reconciled_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
