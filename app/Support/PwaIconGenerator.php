<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class PwaIconGenerator
{
    public const OUTPUT_DIR = 'pwa';

    /** @var array<int> */
    public const SIZES = [180, 192, 512];

    public static function outputDirectory(): string
    {
        return public_path(self::OUTPUT_DIR);
    }

    public static function iconUrl(int $size): string
    {
        return asset(self::OUTPUT_DIR . "/icon-{$size}.png");
    }

    public static function transparentLogoUrl(): string
    {
        self::ensureIcons();

        return asset(self::OUTPUT_DIR . '/logo-transparent.png');
    }

    public static function regenerate(): bool
    {
        File::ensureDirectoryExists(self::outputDirectory());

        $sourcePath = self::resolveSourcePath();

        if ($sourcePath === null) {
            return self::generateDefaultIcons();
        }

        try {
            $processed = self::loadAndRemoveWhiteBackground($sourcePath);

            self::savePng($processed, self::outputDirectory() . '/logo-transparent.png');

            foreach (self::SIZES as $size) {
                $icon = self::renderIcon($processed, $size);
                self::savePng($icon, self::outputDirectory() . "/icon-{$size}.png");
                imagedestroy($icon);
            }

            imagedestroy($processed);

            return true;
        } catch (\Throwable) {
            return self::generateDefaultIcons();
        }
    }

    public static function iconsExist(): bool
    {
        if (! is_file(self::outputDirectory() . '/logo-transparent.png')) {
            return false;
        }

        foreach (self::SIZES as $size) {
            if (! is_file(self::outputDirectory() . "/icon-{$size}.png")) {
                return false;
            }
        }

        return true;
    }

    public static function ensureIcons(): void
    {
        if (! self::iconsExist()) {
            self::regenerate();
        }
    }

    protected static function resolveSourcePath(): ?string
    {
        $logo = AppSettings::get()?->logo;

        if (blank($logo)) {
            return null;
        }

        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            return null;
        }

        $path = storage_path('app/public/' . ltrim($logo, '/'));

        return is_file($path) ? $path : null;
    }

    protected static function loadAndRemoveWhiteBackground(string $path): \GdImage
    {
        $image = self::loadImage($path);

        imagealphablending($image, false);
        imagesavealpha($image, true);

        self::floodFillBackground($image, 30);

        return self::trimTransparent($image);
    }

    protected static function loadImage(string $path): \GdImage
    {
        $info = getimagesize($path);

        if ($info === false) {
            throw new \RuntimeException('Logo tidak dapat dibaca.');
        }

        $image = match ($info[2]) {
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => throw new \RuntimeException('Format logo tidak didukung.'),
        };

        $width = imagesx($image);
        $height = imagesy($image);
        $truecolor = imagecreatetruecolor($width, $height);

        imagealphablending($truecolor, false);
        imagesavealpha($truecolor, true);

        $transparent = imagecolorallocatealpha($truecolor, 0, 0, 0, 127);
        imagefill($truecolor, 0, 0, $transparent);
        imagecopy($truecolor, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);

        return $truecolor;
    }

    protected static function floodFillBackground(\GdImage $image, int $tolerance): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $visited = [];
        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);

        $queue = [
            [0, 0],
            [$width - 1, 0],
            [0, $height - 1],
            [$width - 1, $height - 1],
        ];

        while ($queue !== []) {
            [$x, $y] = array_shift($queue);

            if ($x < 0 || $y < 0 || $x >= $width || $y >= $height) {
                continue;
            }

            if (isset($visited["{$x}:{$y}"])) {
                continue;
            }

            $visited["{$x}:{$y}"] = true;
            [$red, $green, $blue] = self::pixelRgb($image, $x, $y);

            if (! self::isBackgroundColor($red, $green, $blue, $tolerance)) {
                continue;
            }

            imagesetpixel($image, $x, $y, $transparent);
            $queue[] = [$x + 1, $y];
            $queue[] = [$x - 1, $y];
            $queue[] = [$x, $y + 1];
            $queue[] = [$x, $y - 1];
        }
    }

    protected static function isBackgroundColor(int $red, int $green, int $blue, int $tolerance): bool
    {
        $min = 255 - $tolerance;

        return $red >= $min && $green >= $min && $blue >= $min;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    protected static function pixelRgb(\GdImage $image, int $x, int $y): array
    {
        $color = imagecolorat($image, $x, $y);

        return [
            ($color >> 16) & 0xFF,
            ($color >> 8) & 0xFF,
            $color & 0xFF,
        ];
    }

    protected static function trimTransparent(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $minX = $width;
        $minY = $height;
        $maxX = 0;
        $maxY = 0;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $alpha = (imagecolorat($image, $x, $y) >> 24) & 0x7F;

                if ($alpha >= 127) {
                    continue;
                }

                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            }
        }

        if ($maxX < $minX || $maxY < $minY) {
            return $image;
        }

        $cropWidth = $maxX - $minX + 1;
        $cropHeight = $maxY - $minY + 1;
        $cropped = imagecreatetruecolor($cropWidth, $cropHeight);

        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);

        $transparent = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
        imagefill($cropped, 0, 0, $transparent);
        imagecopy($cropped, $image, 0, 0, $minX, $minY, $cropWidth, $cropHeight);
        imagedestroy($image);

        return $cropped;
    }

    protected static function renderIcon(\GdImage $source, int $size): \GdImage
    {
        $srcWidth = imagesx($source);
        $srcHeight = imagesy($source);
        $padding = (int) round($size * 0.12);
        $maxInner = $size - ($padding * 2);
        $scale = min($maxInner / $srcWidth, $maxInner / $srcHeight);
        $targetWidth = max(1, (int) round($srcWidth * $scale));
        $targetHeight = max(1, (int) round($srcHeight * $scale));
        $offsetX = (int) floor(($size - $targetWidth) / 2);
        $offsetY = (int) floor(($size - $targetHeight) / 2);

        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        imagecopyresampled(
            $canvas,
            $source,
            $offsetX,
            $offsetY,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $srcWidth,
            $srcHeight,
        );

        return $canvas;
    }

    protected static function savePng(\GdImage $image, string $path): void
    {
        imagepng($image, $path, 9);
    }

    protected static function generateDefaultIcons(): bool
    {
        foreach (self::SIZES as $size) {
            $icon = self::renderDefaultIcon($size);
            self::savePng($icon, self::outputDirectory() . "/icon-{$size}.png");

            if ($size === 512) {
                copy(
                    self::outputDirectory() . '/icon-512.png',
                    self::outputDirectory() . '/logo-transparent.png',
                );
            }

            imagedestroy($icon);
        }

        return true;
    }

    protected static function renderDefaultIcon(int $size): \GdImage
    {
        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        $amber = imagecolorallocatealpha($canvas, 217, 119, 6, 0);
        $white = imagecolorallocatealpha($canvas, 255, 255, 255, 0);
        $radius = (int) round($size * 0.38);
        $center = (int) round($size / 2);

        imagefilledellipse($canvas, $center, $center, $radius * 2, $radius * 2, $amber);

        $initial = strtoupper(substr(AppSettings::appName(), 0, 1));
        $fontSize = (int) round($size * 0.34);

        if (function_exists('imagettfbbox')) {
            $fontPath = self::defaultFontPath();

            if ($fontPath !== null) {
                $box = imagettfbbox($fontSize, 0, $fontPath, $initial);
                $textWidth = abs($box[2] - $box[0]);
                $textHeight = abs($box[7] - $box[1]);
                $textX = (int) round($center - ($textWidth / 2));
                $textY = (int) round($center + ($textHeight / 2));
                imagettftext($canvas, $fontSize, 0, $textX, $textY, $white, $fontPath, $initial);

                return $canvas;
            }
        }

        imagestring($canvas, 5, (int) ($center - 4), (int) ($center - 8), $initial, $white);

        return $canvas;
    }

    protected static function defaultFontPath(): ?string
    {
        $candidates = [
            'C:/Windows/Fonts/arialbd.ttf',
            'C:/Windows/Fonts/segoeuib.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
