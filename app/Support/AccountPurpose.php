<?php

namespace App\Support;

class AccountPurpose
{
    public const GENERAL = 'general';

    public const BRILINK = 'brilink';

    public const SERVICE = 'service';

    public const UPAH_KERJA = 'upah_kerja';

    public static function labels(): array
    {
        return [
            self::GENERAL => 'Umum (PIC / Kas Harian)',
            self::BRILINK => 'Brilink',
            self::SERVICE => 'Service',
            self::UPAH_KERJA => 'Upah Kerja',
        ];
    }

    public static function fromCategorySlug(string $slug): string
    {
        return match ($slug) {
            'brilink' => self::BRILINK,
            'service' => self::SERVICE,
            'upah_kerja' => self::UPAH_KERJA,
            default => self::GENERAL,
        };
    }

    public static function label(string $purpose): string
    {
        return self::labels()[$purpose] ?? $purpose;
    }
}
