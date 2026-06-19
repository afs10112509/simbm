<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FinancialReportExport
{
    public function __construct(
        public ?string $tanggalMulai = null,
        public ?string $tanggalSelesai = null,
        public ?int $branchId = null,
        public ?string $type = null,
        public ?int $categoryId = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $branchId = $request->integer('branch_id') ?: null;

        if (! AccessControl::canViewAllBranches()) {
            $branchId = AccessControl::userBranchId();
        }

        return new self(
            tanggalMulai: $request->string('tanggal_mulai')->toString() ?: null,
            tanggalSelesai: $request->string('tanggal_selesai')->toString() ?: null,
            branchId: $branchId,
            type: $request->string('type')->toString() ?: null,
            categoryId: $request->integer('category_id') ?: null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function exportQueryParams(): array
    {
        return array_filter([
            'tanggal_mulai' => $this->tanggalMulai,
            'tanggal_selesai' => $this->tanggalSelesai,
            'branch_id' => $this->branchId,
            'type' => $this->type,
            'category_id' => $this->categoryId,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public function baseQuery(): Builder
    {
        $query = Transaction::query()
            ->forLedgerReport()
            ->with(['branch', 'category', 'account']);

        if (! AccessControl::canViewAllBranches()) {
            $query->where('branch_id', AccessControl::userBranchId());
        }

        return $query
            ->when(
                $this->branchId,
                fn (Builder $query) => $query->where('branch_id', $this->branchId)
            )
            ->when(
                $this->type,
                fn (Builder $query) => $query->where('type', $this->type)
            )
            ->when(
                $this->categoryId,
                fn (Builder $query) => $query->where('transaction_category_id', $this->categoryId)
            )
            ->when(
                $this->tanggalMulai,
                fn (Builder $query) => $query->whereDate('transaction_date', '>=', $this->tanggalMulai)
            )
            ->when(
                $this->tanggalSelesai,
                fn (Builder $query) => $query->whereDate('transaction_date', '<=', $this->tanggalSelesai)
            );
    }

    public function transactions(): Collection
    {
        return $this->baseQuery()
            ->latest('transaction_date')
            ->latest('id')
            ->get()
            ->map(function (Transaction $transaction) {
                if (filled($transaction->description)) {
                    $transaction->description = self::sanitizeUtf8($transaction->description);
                }

                return $transaction;
            });
    }

    public function totalPemasukan(): float
    {
        return (float) (clone $this->baseQuery())
            ->where('type', 'income')
            ->sum('amount');
    }

    public function totalPengeluaran(): float
    {
        return (float) (clone $this->baseQuery())
            ->where('type', 'expense')
            ->sum('amount');
    }

    public function laba(): float
    {
        return $this->totalPemasukan() - $this->totalPengeluaran();
    }

    /**
     * @return array<string, string>
     */
    public function filterSummary(): array
    {
        $summary = [];

        if ($this->tanggalMulai || $this->tanggalSelesai) {
            $mulai = $this->tanggalMulai
                ? \Carbon\Carbon::parse($this->tanggalMulai)->translatedFormat('d M Y')
                : 'Awal';
            $selesai = $this->tanggalSelesai
                ? \Carbon\Carbon::parse($this->tanggalSelesai)->translatedFormat('d M Y')
                : 'Akhir';
            $summary['Periode'] = "{$mulai} s/d {$selesai}";
        }

        if ($this->branchId && AccessControl::canViewAllBranches()) {
            $summary['Cabang'] = Branch::query()->whereKey($this->branchId)->value('name') ?? '-';
        }

        if ($this->type) {
            $summary['Jenis'] = $this->type === 'income' ? 'Pemasukan' : 'Pengeluaran';
        }

        if ($this->categoryId) {
            $summary['Kategori'] = TransactionCategory::query()->whereKey($this->categoryId)->value('name') ?? '-';
        }

        return $summary;
    }

    public function downloadPdf()
    {
        return Pdf::loadView('exports.financial-report-pdf', [
            'appName' => self::sanitizeUtf8(AppSettings::appName()),
            'transactions' => $this->transactions(),
            'totalPemasukan' => $this->totalPemasukan(),
            'totalPengeluaran' => $this->totalPengeluaran(),
            'laba' => $this->laba(),
            'filterSummary' => $this->filterSummary(),
            'showBranch' => AccessControl::canViewAllBranches(),
            'generatedAt' => now()->translatedFormat('d M Y H:i'),
        ])
            ->setPaper('a4', 'landscape')
            ->download('laporan-keuangan.pdf');
    }

    public static function sanitizeUtf8(?string $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $clean = mb_convert_encoding($value, 'UTF-8', 'UTF-8');

        return iconv('UTF-8', 'UTF-8//IGNORE', $clean) ?: '-';
    }
}
