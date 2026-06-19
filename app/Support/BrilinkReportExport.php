<?php

namespace App\Support;

use App\Models\Branch;
use App\Services\BrilinkSnapshotService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BrilinkReportExport
{
    public function __construct(
        public int $month,
        public int $year,
        public ?int $branchId = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $branchId = $request->integer('branch_id') ?: null;

        if (! AccessControl::canViewAllBranches()) {
            $branchId = AccessControl::userBranchId();
        }

        return new self(
            month: (int) $request->input('month', now()->month),
            year: (int) $request->input('year', now()->year),
            branchId: $branchId,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function exportQueryParams(): array
    {
        return array_filter([
            'month' => $this->month,
            'year' => $this->year,
            'branch_id' => $this->branchId,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public function periodStart(): Carbon
    {
        return Carbon::create($this->year, $this->month, 1)->startOfMonth();
    }

    public function periodEnd(): Carbon
    {
        return $this->periodStart()->copy()->endOfMonth();
    }

    public function sections()
    {
        $branches = Branch::query()
            ->where('type', AccessControl::BRANCH_KONTER)
            ->when($this->branchId, fn ($query) => $query->whereKey($this->branchId))
            ->orderBy('name')
            ->get();

        return $branches->map(function (Branch $branch) {
            $rows = BrilinkSnapshotService::reportRows(
                $branch->id,
                $this->periodStart(),
                $this->periodEnd()
            );

            return [
                'branch' => $branch,
                'rows' => $rows,
                'totalUntung' => (float) $rows->sum('untung'),
            ];
        });
    }

    public function grandTotal(): float
    {
        return (float) $this->sections()->sum('totalUntung');
    }

    public function downloadPdf()
    {
        return Pdf::loadView('exports.brilink-report-pdf', [
            'appName' => FinancialReportExport::sanitizeUtf8(AppSettings::appName()),
            'periodLabel' => $this->periodStart()->translatedFormat('F Y'),
            'sections' => $this->sections(),
            'grandTotal' => $this->grandTotal(),
            'generatedAt' => now()->translatedFormat('d M Y H:i'),
        ])
            ->setPaper('a4', 'landscape')
            ->download('laporan-brilink.pdf');
    }
}
