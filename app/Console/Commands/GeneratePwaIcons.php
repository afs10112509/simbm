<?php

namespace App\Console\Commands;

use App\Support\PwaIconGenerator;
use Illuminate\Console\Command;

class GeneratePwaIcons extends Command
{
    protected $signature = 'simbm:generate-pwa-icons';

    protected $description = 'Generate PWA icons from logo (white background removed)';

    public function handle(): int
    {
        $this->info('Generating PWA icons...');

        if (! PwaIconGenerator::regenerate()) {
            $this->error('Failed to generate PWA icons.');

            return self::FAILURE;
        }

        foreach (PwaIconGenerator::SIZES as $size) {
            $this->line('  ✓ icon-' . $size . '.png');
        }

        $this->info('Done. Icons saved to public/pwa/');

        return self::SUCCESS;
    }
}
