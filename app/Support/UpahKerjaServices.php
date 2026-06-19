<?php

namespace App\Support;

class UpahKerjaServices
{
    public static function labels(): array
    {
        return [
            'servis_ringan' => 'Servis Ringan',
            'servis_berat' => 'Servis Berat',
            'ganti_oli' => 'Ganti Oli',
            'spooring' => 'Spooring / Balancing',
            'body_las' => 'Body / Las',
            'cat' => 'Cat',
            'tune_up' => 'Tune Up',
            'cuci_motor' => 'Cuci Motor',
            'lainnya' => 'Lainnya',
        ];
    }

    public static function label(string $key): string
    {
        return self::labels()[$key] ?? $key;
    }
}
