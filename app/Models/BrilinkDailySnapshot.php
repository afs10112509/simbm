<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrilinkDailySnapshot extends Model
{
    protected $fillable = [
        'branch_id',
        'user_id',
        'snapshot_date',
        'total_balance',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'total_balance' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lines()
    {
        return $this->hasMany(BrilinkDailySnapshotLine::class);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
