<?php

namespace App\Models;

use App\Support\AppSettings;
use App\Support\PwaIconGenerator;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'app_name',
        'company_name',
        'address',
        'phone',
        'email',
        'currency',
        'logo',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            AppSettings::clearCache();
            PwaIconGenerator::regenerate();
        });

        static::deleted(function () {
            AppSettings::clearCache();
            PwaIconGenerator::regenerate();
        });
    }
}
