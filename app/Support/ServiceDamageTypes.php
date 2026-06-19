<?php

namespace App\Support;

class ServiceDamageTypes
{
    public static function labels(): array
    {
        return [
            'lcd' => 'LCD',
            'lem' => 'LEM',
            'konektor' => 'KONEKTOR',
            'pola' => 'POLA',
            'batt' => 'BATT',
            'ic' => 'IC',
            'flexible' => 'FLEXIBLE',
            'backdoor' => 'BACKDOOR',
            'speaker' => 'SPEAKER',
            'lainnya' => 'LAINNYA',
        ];
    }

    public static function label(string $key): string
    {
        return self::labels()[$key] ?? strtoupper($key);
    }
}
