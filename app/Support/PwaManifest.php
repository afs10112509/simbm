<?php

namespace App\Support;

class PwaManifest
{
    public static function ensureReady(): void
    {
        PwaIconGenerator::ensureIcons();
    }

    public static function url(): string
    {
        return url('/manifest.webmanifest');
    }

    /**
     * @return array<string, mixed>
     */
    public static function data(): array
    {
        self::ensureReady();

        $appName = AppSettings::appName();
        $shortName = self::shortName($appName);

        return [
            'name' => $appName,
            'short_name' => $shortName,
            'description' => 'Sistem manajemen keuangan ' . $appName,
            'start_url' => url('/admin'),
            'scope' => url('/admin'),
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => '#030712',
            'theme_color' => '#d97706',
            'lang' => 'id',
            'icons' => [
                [
                    'src' => PwaIconGenerator::iconUrl(192),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => PwaIconGenerator::iconUrl(512),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => PwaIconGenerator::iconUrl(512),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ];
    }

    public static function headHtml(): string
    {
        self::ensureReady();

        $manifest = e(self::url());
        $themeColor = '#d97706';
        $appleIcon = e(PwaIconGenerator::iconUrl(180));

        return implode('', [
            '<link rel="manifest" href="' . $manifest . '">',
            '<meta name="mobile-web-app-capable" content="yes">',
            '<meta name="apple-mobile-web-app-capable" content="yes">',
            '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">',
            '<meta name="apple-mobile-web-app-title" content="' . e(self::shortName(AppSettings::appName())) . '">',
            '<meta name="theme-color" content="' . $themeColor . '">',
            '<link rel="apple-touch-icon" href="' . $appleIcon . '">',
            '<link rel="stylesheet" href="' . asset('css/simbm-mobile.css') . '?v=1">',
        ]);
    }

    protected static function shortName(string $appName): string
    {
        $short = trim($appName);

        if (mb_strlen($short) <= 12) {
            return $short;
        }

        return rtrim(mb_strimwidth($short, 0, 12, ''));
    }
}
