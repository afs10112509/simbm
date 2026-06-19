<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\ServiceRecord;
use App\Models\ServiceTechnician;
use App\Support\AccessControl;
use App\Support\RecordDateTime;
use App\Support\ResponsiveForm;
use App\Support\ServiceDamageTypes;
use App\Support\ServiceReportExport;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class ServiceReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Service';

    protected static ?string $navigationGroup = 'Konter';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.service-report';

    public ?array $filterData = [];

    public function getTitle(): string
    {
        return 'Laporan Service';
    }

    public static function canAccess(): bool
    {
        return AccessControl::canViewServiceReport();
    }

    public function mount(): void
    {
        $this->form->fill([
            'tanggal_mulai' => now()->startOfMonth()->format('Y-m-d'),
            'tanggal_selesai' => now()->format('Y-m-d'),
            'branch_id' => AccessControl::canViewAllBranches() ? null : auth()->user()->branch_id,
            'service_technician_id' => null,
        ]);
    }

    protected function exportFilters(): ServiceReportExport
    {
        $filters = $this->filterData ?? [];

        return new ServiceReportExport(
            tanggalMulai: $filters['tanggal_mulai'] ?? null,
            tanggalSelesai: $filters['tanggal_selesai'] ?? null,
            branchId: AccessControl::canViewAllBranches()
                ? ($filters['branch_id'] ?? null)
                : AccessControl::userBranchId(),
            serviceTechnicianId: $filters['service_technician_id'] ?? null,
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
                        'exports.service-report.pdf',
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
                Forms\Components\Grid::make(ResponsiveForm::columns(4))
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

                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->placeholder('Semua Cabang Konter')
                            ->options(fn () => Branch::query()
                                ->where('type', AccessControl::BRANCH_KONTER)
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->visible(fn () => AccessControl::canViewAllBranches())
                            ->default(fn () => AccessControl::canViewAllBranches() ? null : auth()->user()->branch_id),

                        Forms\Components\Select::make('service_technician_id')
                            ->label('Tukang Service')
                            ->placeholder('Semua Tukang')
                            ->options(function (Get $get) use ($branchId) {
                                $filterBranch = AccessControl::canViewAllBranches()
                                    ? $get('branch_id')
                                    : $branchId;

                                return ServiceTechnician::query()
                                    ->when($filterBranch, fn (Builder $query) => $query->where('branch_id', $filterBranch))
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->live(),
                    ]),
            ])
            ->statePath('filterData');
    }

    protected function baseQuery(): Builder
    {
        $filters = $this->filterData ?? [];

        $query = ServiceRecord::query()
            ->with(['branch', 'technician']);

        if (! AccessControl::canViewAllBranches()) {
            $query->where('branch_id', auth()->user()->branch_id);
        }

        return $query
            ->when(
                $filters['branch_id'] ?? null,
                fn (Builder $query, int $branchId) => $query->where('branch_id', $branchId)
            )
            ->when(
                $filters['tanggal_mulai'] ?? null,
                fn (Builder $query, string $date) => $query->whereDate('service_date', '>=', $date)
            )
            ->when(
                $filters['tanggal_selesai'] ?? null,
                fn (Builder $query, string $date) => $query->whereDate('service_date', '<=', $date)
            )
            ->when(
                $filters['service_technician_id'] ?? null,
                fn (Builder $query, int $technicianId) => $query->where('service_technician_id', $technicianId)
            );
    }

    public function getRecords()
    {
        return $this->baseQuery()
            ->latest('service_date')
            ->latest('id')
            ->get();
    }

    public function getTotalPrice(): float
    {
        return (float) $this->baseQuery()->sum('price');
    }

    public function getTotalModal(): float
    {
        return (float) $this->baseQuery()->sum('modal');
    }

    public function getTotalProfit(): float
    {
        return (float) $this->baseQuery()->sum('profit');
    }

    public function getSplitProfit(): float
    {
        return $this->getTotalProfit() / 2;
    }

    public function formatDateTime(ServiceRecord $record): string
    {
        return RecordDateTime::format($record->service_date, $record->created_at);
    }

    public function damageLabel(string $type): string
    {
        return ServiceDamageTypes::label($type);
    }
}
