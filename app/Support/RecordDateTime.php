<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class RecordDateTime
{
    public static function format(
        ?CarbonInterface $date,
        ?CarbonInterface $recordedAt,
        string $dateFormat = 'd M Y',
        string $timeFormat = 'H:i',
    ): string {
        if (! $date) {
            return '-';
        }

        $datePart = $date->translatedFormat($dateFormat);
        $timePart = $recordedAt?->format($timeFormat) ?? '-';

        return "{$datePart} {$timePart}";
    }

    public static function forTransaction(Model $transaction): string
    {
        return self::format(
            $transaction->transaction_date,
            $transaction->created_at,
        );
    }

    public static function forTransfer(Model $transfer): string
    {
        return self::format(
            $transfer->transfer_date,
            $transfer->created_at,
        );
    }
}
