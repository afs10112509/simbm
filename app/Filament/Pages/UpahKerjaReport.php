<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\Worker;
use App\Support\AccessControl;
use App\Support\ResponsiveForm;
use App\Support\UpahKerjaReportExport;
use App\Support\UpahKerjaServices;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class UpahKerjaReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Upah Kerja';

    protected static ?string $navigationGroup = 'Bengkel';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.upah-kerja-report';

    public ?array $filterData = [];

    public function getTitle(): string
    {
        return 'Laporan Upah Kerja';
    }

    public static function canAccess(): bool
    {
        return AccessControl::canViewUpahKerjaReport();
    }

    public function mount(): void
    {
        $this->form->fill([
            'tanggal_mulai' => now()->startOfMonth()->format('Y-m-d'),
            'tanggal_selesai' => now()->format('Y-m-d'),
            'worker_id' => null,
        ]);
    }

    protected function exportFilters(): UpahKerjaReportExport
    {
        $filters = $this->filterData ?? [];

        return new UpahKerjaReportExport(
            tanggalMulai: $filters['tanggal_mulai'] ?? null,
            tanggalSelesai: $filters['tanggal_selesai'] ?? null,
            workerId: $filters['worker_id'] ?? null,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_pdf')
                ->label('Ekspor PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function (): void {
                    $this->redirectRoute(
                        'exports.upah-kerja-report.pdf',
                        $this->exportFilters()->exportQueryParams(),
                    );
                }),
        ];
    }

    public function form(Form $form): Form
    {
        $branchId = AccessControl::canViewAllBranches()
            ? null
            : auth()->user()->branch_id;

        return $form
            ->schema([
                Forms\Components\Grid::make(ResponsiveForm::columns(3))
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live(),

                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live(),

                        Forms\Components\Select::make('worker_id')
                            ->label('Nama Pekerja')
                            ->placeholder('Semua Pekerja')
                            ->options(fn () => Worker::query()
                                ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->live(),
                    ]),
            ])
            ->statePath('filterData');
    }

    protected function baseQuery(): Builder
    {
        $filters = $this->filterData ?? [];
        $categoryId = TransactionCategory::findBySlug('upah_kerja')?->id;

        $query = Transaction::query()
            ->with(['worker', 'user'])
            ->when(
                $categoryId,
                fn (Builder $query) => $query->where('transaction_category_id', $categoryId)
            );

        if (! AccessControl::canViewAllBranches()) {
            $query->where('branch_id', auth()->user()->branch_id);
        }

        return $query
            ->when(
                $filters['tanggal_mulai'] ?? null,
                fn (Builder $query, string $date) => $query->whereDate('transaction_date', '>=', $date)
            )
            ->when(
                $filters['tanggal_selesai'] ?? null,
                fn (Builder $query, string $date) => $query->whereDate('transaction_date', '<=', $date)
            )
            ->when(
                $filters['worker_id'] ?? null,
                fn (Builder $query, int $workerId) => $query->where('worker_id', $workerId)
            );
    }

    public function getRecords()
    {
        return $this->baseQuery()
            ->latest('transaction_date')
            ->latest('id')
            ->get();
    }

    public function getTotalUpah(): float
    {
        return (float) $this->baseQuery()->sum('amount');
    }

    public function getServiceLabel(?string $serviceType): string
    {
        return $serviceType ? UpahKerjaServices::label($serviceType) : '-';
    }
}
