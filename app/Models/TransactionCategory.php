<?php

namespace App\Models;

use App\Support\AccessControl;
use Illuminate\Database\Eloquent\Model;

class TransactionCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'branch_type',
        'is_active',
    ];

    public function scopeForBranchType($query, ?string $branchType)
    {
        return $query->where(function ($query) use ($branchType) {
            $query->whereNull('branch_type')
                ->orWhere('branch_type', $branchType);
        });
    }

    public function scopeAvailableForPic($query, Branch $branch)
    {
        return $query->forBranchType($branch->type);
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
