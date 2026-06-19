<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Services\BrilinkSnapshotService;
use App\Support\AccessControl;
use App\Support\BrilinkReportExport;
use App\Support\ResponsiveForm;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class BrilinkReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Brilink';

    protected static ?string $navigationGroup = 'Konter';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.brilink-report';

    public ?array $filterData = [];

    public function getTitle(): string
    {
        return 'Laporan Brilink';
    }

    public static function canAccess(): bool
    {
        return AccessControl::canAccessBrilink();
    }

    public function mount(): void
    {
        $this->form->fill([
            'month' => now()->month,
            'year' => now()->year,
            'branch_id' => AccessControl::canViewAllBranches() ? null : auth()->user()->branch_id,
        ]);
    }

    protected function exportFilters(): BrilinkReportExport
    {
        return new BrilinkReportExport(
            month: (int) ($this->filterData['month'] ?? now()->month),
            year: (int) ($this->filterData['year'] ?? now()->year),
            branchId: $this->resolvedBranchId(),
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
                        'exports.brilink-report.pdf',
                        $this->exportFilters()->exportQueryParams(),
                    );
                }),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(ResponsiveForm::columns(3))
                    ->schema([
                        Forms\Components\Select::make('month')
                            ->label('Bulan')
                            ->options(collect(range(1, 12))->mapWithKeys(fn (int $month) => [
                                $month => Carbon::create(null, $month, 1)->translatedFormat('F'),
                            ]))
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('year')
                            ->label('Tahun')
                            ->options(collect(range(now()->year - 2, now()->year))->mapWithKeys(fn (int $year) => [
                                $year => (string) $year,
                            ]))
                            ->required()
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
                    ]),
            ])
            ->statePath('filterData');
    }

    protected function resolvedBranchId(): ?int
    {
        if (AccessControl::canViewAllBranches()) {
            return $this->filterData['branch_id'] ?? null;
        }

        return auth()->user()->branch_id;
    }

    protected function periodStart(): Carbon
    {
        return Carbon::create(
            (int) ($this->filterData['year'] ?? now()->year),
            (int) ($this->filterData['month'] ?? now()->month),
            1
        )->startOfMonth();
    }

    protected function periodEnd(): Carbon
    {
        return $this->periodStart()->copy()->endOfMonth();
    }

    public function getReportSections(): Collection
    {
        $branchId = $this->resolvedBranchId();

        $branches = Branch::query()
            ->where('type', AccessControl::BRANCH_KONTER)
            ->when($branchId, fn ($query) => $query->whereKey($branchId))
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
                'gapCount' => $rows->where('hasGapWarning', true)->count(),
            ];
        });
    }

    public function getGrandTotalUntung(): float
    {
        return (float) $this->getReportSections()->sum('totalUntung');
    }
}
