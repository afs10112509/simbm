<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodLock extends Model
{
    protected $fillable = [
        'year',
        'month',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->translatedFormat('F Y');
    }
}
