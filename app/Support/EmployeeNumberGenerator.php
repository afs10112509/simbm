<?php

namespace App\Support;

use App\Models\Employee;

class EmployeeNumberGenerator
{
    public static function next(): string
    {
        $maxSequence = Employee::query()
            ->where('employee_number', 'like', 'BG%')
            ->pluck('employee_number')
            ->map(fn (string $number): int => (int) substr($number, 2))
            ->max();

        $next = ($maxSequence ?? 0) + 1;

        return 'BG' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
