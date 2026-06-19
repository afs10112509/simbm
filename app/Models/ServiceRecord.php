<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRecord extends Model
{
    protected $fillable = [
        'branch_id',
        'service_technician_id',
        'user_id',
        'account_id',
        'income_transaction_id',
        'expense_transaction_id',
        'service_date',
        'device_brand',
        'device_type',
        'damage_type',
        'modal',
        'price',
        'profit',
    ];

    protected $casts = [
        'service_date' => 'date',
        'modal' => 'decimal:2',
        'price' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function technician()
    {
        return $this->belongsTo(ServiceTechnician::class, 'service_technician_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function incomeTransaction()
    {
        return $this->belongsTo(Transaction::class, 'income_transaction_id');
    }

    public function expenseTransaction()
    {
        return $this->belongsTo(Transaction::class, 'expense_transaction_id');
    }

    public static function calculateProfit(float $price, float $modal): float
    {
        return max($price - $modal, 0);
    }
}
