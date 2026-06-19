<?php

namespace App\Models;

use App\Support\AccessControl;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'phone',
        'address',
        'is_active',
    ];

    public function isKonter(): bool
    {
        return $this->type === AccessControl::BRANCH_KONTER;
    }

    public function isBengkel(): bool
    {
        return $this->type === AccessControl::BRANCH_BENGKEL;
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function workers()
    {
        return $this->hasMany(Worker::class);
    }

    public function serviceTechnicians()
    {
        return $this->hasMany(ServiceTechnician::class);
    }

    public function serviceRecords()
    {
        return $this->hasMany(ServiceRecord::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function brilinkDailySnapshots()
    {
        return $this->hasMany(BrilinkDailySnapshot::class);
    }
}
