<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class AppSettings
{
    protected static ?Setting $cached = null;

    public static function clearCache(): void
    {
        static::$cached = null;
    }

    public static function get(): ?Setting
    {
        if (static::$cached !== null) {
            return static::$cached;
        }

        try {
            if (! Schema::hasTable('settings')) {
                return null;
            }

            static::$cached = Setting::query()->first();
        } catch (\Throwable) {
            return null;
        }

        return static::$cached;
    }

    public static function appName(): string
    {
        return static::get()?->app_name ?: config('app.name', 'SIMBM');
    }

    public static function companyName(): ?string
    {
        return static::get()?->company_name;
    }

    public static function address(): ?string
    {
        return static::get()?->address;
    }

    public static function phone(): ?string
    {
        return static::get()?->phone;
    }

    public static function email(): ?string
    {
        return static::get()?->email;
    }

    public static function currency(): string
    {
        return static::get()?->currency ?: 'IDR';
    }

    public static function logoUrl(): ?string
    {
        $logo = static::get()?->logo;

        if (blank($logo)) {
            return null;
        }

        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            return $logo;
        }

        return '/storage/' . ltrim($logo, '/');
    }
}
