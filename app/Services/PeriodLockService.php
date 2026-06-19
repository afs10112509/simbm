<?php

namespace App\Services;

use App\Models\PeriodLock;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PeriodLockService
{
    public static function isLocked(Carbon|string|null $date): bool
    {
        if ($date === null) {
            return false;
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return PeriodLock::query()
            ->where('year', $carbon->year)
            ->where('month', $carbon->month)
            ->exists();
    }

    public static function assertEditable(Carbon|string|null $date, string $field = 'transaction_date'): void
    {
        if (\App\Support\AccessControl::isOwner()) {
            return;
        }

        if (! self::isLocked($date)) {
            return;
        }

        $label = $date instanceof Carbon
            ? $date->translatedFormat('F Y')
            : Carbon::parse($date)->translatedFormat('F Y');

        throw ValidationException::withMessages([
            $field => "Periode {$label} sudah dikunci. Hubungi pemilik untuk membuka kunci.",
        ]);
    }

    public static function lockMonth(int $year, int $month, int $userId): PeriodLock
    {
        return PeriodLock::query()->updateOrCreate(
            [
                'year' => $year,
                'month' => $month,
            ],
            [
                'user_id' => $userId,
            ],
        );
    }

    public static function unlockMonth(int $year, int $month): void
    {
        PeriodLock::query()
            ->where('year', $year)
            ->where('month', $month)
            ->delete();
    }

    /**
     * @return Collection<int, PeriodLock>
     */
    public static function lockedMonths(): Collection
    {
        return PeriodLock::query()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();
    }

    public static function latestLockedLabel(): ?string
    {
        $lock = self::lockedMonths()->first();

        return $lock?->label();
    }
}
