<?php

namespace App\Support;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\Worker;
use App\Support\UpahKerjaServices;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UpahKerjaReportExport
{
    public function __construct(
        public ?string $tanggalMulai = null,
        public ?string $tanggalSelesai = null,
        public ?int $workerId = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            tanggalMulai: $request->string('tanggal_mulai')->toString() ?: null,
            tanggalSelesai: $request->string('tanggal_selesai')->toString() ?: null,
            workerId: $request->integer('worker_id') ?: null,
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
            'worker_id' => $this->workerId,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public function baseQuery(): Builder
    {
        $categoryId = TransactionCategory::findBySlug('upah_kerja')?->id;

        $query = Transaction::query()->with(['worker', 'user']);

        if ($categoryId) {
            $query->where('transaction_category_id', $categoryId);
        }

        if (! AccessControl::canViewAllBranches()) {
            $query->where('branch_id', AccessControl::userBranchId());
        }

        return $query
            ->when($this->tanggalMulai, fn (Builder $q) => $q->whereDate('transaction_date', '>=', $this->tanggalMulai))
            ->when($this->tanggalSelesai, fn (Builder $q) => $q->whereDate('transaction_date', '<=', $this->tanggalSelesai))
            ->when($this->workerId, fn (Builder $q) => $q->where('worker_id', $this->workerId));
    }

    public function records()
    {
        return $this->baseQuery()->latest('transaction_date')->latest('id')->get();
    }

    public function totalUpah(): float
    {
        return (float) $this->baseQuery()->sum('amount');
    }

    public function filterSummary(): array
    {
        $summary = [];

        if ($this->tanggalMulai || $this->tanggalSelesai) {
            $summary['Periode'] = ($this->tanggalMulai ?: 'Awal') . ' s/d ' . ($this->tanggalSelesai ?: 'Akhir');
        }

        if ($this->workerId) {
            $summary['Pekerja'] = Worker::query()->whereKey($this->workerId)->value('name') ?? '-';
        }

        return $summary;
    }

    public function downloadPdf()
    {
        return Pdf::loadView('exports.upah-kerja-report-pdf', [
            'appName' => FinancialReportExport::sanitizeUtf8(AppSettings::appName()),
            'records' => $this->records(),
            'totalUpah' => $this->totalUpah(),
            'filterSummary' => $this->filterSummary(),
            'generatedAt' => now()->translatedFormat('d M Y H:i'),
        ])
            ->setPaper('a4', 'landscape')
            ->download('laporan-upah-kerja.pdf');
    }
}
