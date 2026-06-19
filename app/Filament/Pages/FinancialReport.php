<?php

namespace App\Filament\Pages;

use App\Exports\TransactionExport;
use App\Models\Branch;
use App\Models\TransactionCategory;
use App\Support\AccessControl;
use App\Support\FinancialReportExport;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class FinancialReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Laporan Keuangan';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.financial-report';

    public ?string $tanggalMulai = null;

    public ?string $tanggalSelesai = null;

    public ?int $branchId = null;

    public ?string $type = null;

    public ?int $categoryId = null;

    public static function canAccess(): bool
    {
        return AccessControl::canViewFinancialReport();
    }

    public function getTitle(): string
    {
        return 'Laporan Keuangan';
    }

    public function mount(): void
    {
        if (! AccessControl::canViewAllBranches()) {
            $this->branchId = auth()->user()->branch_id;
        }
    }

    public function updatedType(): void
    {
        $this->categoryId = null;
    }

    protected function exportFilters(): FinancialReportExport
    {
        return new FinancialReportExport(
            tanggalMulai: $this->tanggalMulai,
            tanggalSelesai: $this->tanggalSelesai,
            branchId: $this->branchId,
            type: $this->type,
            categoryId: $this->categoryId,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_excel')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action('exportExcel'),

            \Filament\Actions\Action::make('export_pdf')
                ->label('Ekspor PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function (): void {
                    $this->redirectRoute(
                        'exports.financial-report.pdf',
                        $this->exportFilters()->exportQueryParams(),
                    );
                }),
        ];
    }

    /**
     * Fallback untuk snapshot Livewire lama yang masih memanggil exportPdf().
     */
    public function exportPdf(): void
    {
        $this->redirectRoute(
            'exports.financial-report.pdf',
            $this->exportFilters()->exportQueryParams(),
        );
    }

    public function exportExcel()
    {
        return Excel::download(
            new TransactionExport($this->baseTransactionQuery()),
            'laporan-keuangan.xlsx'
        );
    }

    protected function baseTransactionQuery(): Builder
    {
        return $this->exportFilters()->baseQuery();
    }

    public function getTransactions()
    {
        return $this->exportFilters()->transactions();
    }

    public function getTotalPemasukan(): float
    {
        return $this->exportFilters()->totalPemasukan();
    }

    public function getTotalPengeluaran(): float
    {
        return $this->exportFilters()->totalPengeluaran();
    }

    public function getLaba(): float
    {
        return $this->exportFilters()->laba();
    }

    public function getBranches()
    {
        if (! AccessControl::canViewAllBranches()) {
            return Branch::where('id', auth()->user()->branch_id)->get();
        }

        return Branch::orderBy('name')->get();
    }

    public function getCategories()
    {
        return TransactionCategory::query()
            ->where('is_active', true)
            ->when(
                $this->type,
                fn (Builder $query) => $query->where('type', $this->type)
            )
            ->orderBy('name')
            ->get();
    }
}
