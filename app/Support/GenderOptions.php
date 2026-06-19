<?php

namespace App\Support;

class GenderOptions
{
    public const MALE = 'male';

    public const FEMALE = 'female';

    public static function labels(): array
    {
        return [
            self::MALE => 'Laki-laki',
            self::FEMALE => 'Perempuan',
        ];
    }

    public static function label(string $value): string
    {
        return self::labels()[$value] ?? $value;
    }
}
