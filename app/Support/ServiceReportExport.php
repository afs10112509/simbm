<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\ServiceRecord;
use App\Models\ServiceTechnician;
use App\Support\ServiceDamageTypes;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ServiceReportExport
{
    public function __construct(
        public ?string $tanggalMulai = null,
        public ?string $tanggalSelesai = null,
        public ?int $branchId = null,
        public ?int $serviceTechnicianId = null,
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
            serviceTechnicianId: $request->integer('service_technician_id') ?: null,
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
            'service_technician_id' => $this->serviceTechnicianId,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public function baseQuery(): Builder
    {
        $query = ServiceRecord::query()->with(['branch', 'technician']);

        if (! AccessControl::canViewAllBranches()) {
            $query->where('branch_id', AccessControl::userBranchId());
        }

        return $query
            ->when($this->branchId, fn (Builder $q) => $q->where('branch_id', $this->branchId))
            ->when($this->tanggalMulai, fn (Builder $q) => $q->whereDate('service_date', '>=', $this->tanggalMulai))
            ->when($this->tanggalSelesai, fn (Builder $q) => $q->whereDate('service_date', '<=', $this->tanggalSelesai))
            ->when($this->serviceTechnicianId, fn (Builder $q) => $q->where('service_technician_id', $this->serviceTechnicianId));
    }

    public function records()
    {
        return $this->baseQuery()->latest('service_date')->latest('id')->get();
    }

    public function totalPrice(): float
    {
        return (float) $this->baseQuery()->sum('price');
    }

    public function totalModal(): float
    {
        return (float) $this->baseQuery()->sum('modal');
    }

    public function totalProfit(): float
    {
        return (float) $this->baseQuery()->sum('profit');
    }

    public function splitProfit(): float
    {
        return $this->totalProfit() / 2;
    }

    public function filterSummary(): array
    {
        $summary = [];

        if ($this->tanggalMulai || $this->tanggalSelesai) {
            $summary['Periode'] = ($this->tanggalMulai ?: 'Awal') . ' s/d ' . ($this->tanggalSelesai ?: 'Akhir');
        }

        if ($this->branchId && AccessControl::canViewAllBranches()) {
            $summary['Cabang'] = Branch::query()->whereKey($this->branchId)->value('name') ?? '-';
        }

        if ($this->serviceTechnicianId) {
            $summary['Tukang'] = ServiceTechnician::query()->whereKey($this->serviceTechnicianId)->value('name') ?? '-';
        }

        return $summary;
    }

    public function downloadPdf()
    {
        return Pdf::loadView('exports.service-report-pdf', [
            'appName' => FinancialReportExport::sanitizeUtf8(AppSettings::appName()),
            'records' => $this->records(),
            'totalPrice' => $this->totalPrice(),
            'totalModal' => $this->totalModal(),
            'totalProfit' => $this->totalProfit(),
            'splitProfit' => $this->splitProfit(),
            'filterSummary' => $this->filterSummary(),
            'showBranch' => AccessControl::canViewAllBranches(),
            'generatedAt' => now()->translatedFormat('d M Y H:i'),
        ])
            ->setPaper('a4', 'landscape')
            ->download('laporan-service.pdf');
    }
}
