<?php

namespace App\Support;

class ResponsiveForm
{
    /**
     * @return array<string, int>
     */
    public static function columns(int $max): array
    {
        return match ($max) {
            1 => ['default' => 1],
            2 => ['default' => 1, 'md' => 2],
            3 => ['default' => 1, 'sm' => 2, 'lg' => 3],
            4 => ['default' => 1, 'sm' => 2, 'lg' => 4],
            5 => ['default' => 1, 'sm' => 2, 'md' => 3, 'xl' => 5],
            default => ['default' => 1, 'md' => min($max, 2), 'lg' => $max],
        };
    }
}
