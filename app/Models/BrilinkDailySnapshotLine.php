<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrilinkDailySnapshotLine extends Model
{
    protected $fillable = [
        'brilink_daily_snapshot_id',
        'account_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function snapshot()
    {
        return $this->belongsTo(BrilinkDailySnapshot::class, 'brilink_daily_snapshot_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
