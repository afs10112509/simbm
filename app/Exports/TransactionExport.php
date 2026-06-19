<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;

class TransactionExport implements FromCollection
{
    public function __construct(
        protected Builder $query
    ) {}

    public function collection()
    {
        return (clone $this->query)
            ->with(['branch', 'category', 'account'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();
    }
}
