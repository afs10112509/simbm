<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Services\BrilinkSnapshotService;
use App\Services\PeriodLockService;
use App\Support\AccessControl;
use App\Support\AccountBalanceSummary;
use App\Support\AccountPurpose;
use App\Support\NominalInput;
use App\Support\ResponsiveForm;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class BrilinkSaldoInput extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Input Saldo Brilink';

    protected static ?string $navigationGroup = 'Konter';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.brilink-saldo-input';

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Input Saldo Brilink';
    }

    public static function canAccess(): bool
    {
        return AccessControl::canManageBrilink()
            && AccessControl::userBranchId() !== null;
    }

    public function getBrilinkAccounts(): Collection
    {
        $branchId = AccessControl::userBranchId();

        if (! $branchId) {
            return collect();
        }

        return AccountBalanceSummary::accountsForPurpose($branchId, AccountPurpose::BRILINK);
    }

    public function hasBrilinkAccounts(): bool
    {
        return $this->getBrilinkAccounts()->isNotEmpty();
    }

    public function mount(): void
    {
        if (AccessControl::userBranchId() === null) {
            return;
        }

        $this->form->fill([
            'snapshot_date' => now()->format('Y-m-d'),
            'balances' => $this->defaultBalancesForDate(now()->format('Y-m-d')),
        ]);
    }

    protected function defaultBalancesForDate(string $date): array
    {
        $branchId = AccessControl::userBranchId();
        $existing = BrilinkSnapshotService::balancesForDate($branchId, $date);
        $balances = [];

        foreach ($this->getBrilinkAccounts() as $account) {
            if (isset($existing[$account->id])) {
                $balances[$account->id] = $existing[$account->id];
            } else {
                $balances[$account->id] = number_format((float) $account->balance, 0, ',', '.');
            }
        }

        return $balances;
    }

    public function form(Form $form): Form
    {
        $accountFields = $this->getBrilinkAccounts()
            ->map(fn (Account $account) => NominalInput::make("balances.{$account->id}")
                ->label($account->name)
                ->required())
            ->all();

        return $form
            ->schema([
                Forms\Components\Section::make('Saldo Harian Brilink')
                    ->description('Isi saldo akhir setiap akun Brilink. Total dan untung dihitung otomatis.')
                    ->schema([
                        Forms\Components\Placeholder::make('branch_info')
                            ->label('Cabang')
                            ->content(fn () => auth()->user()->branch?->name ?? '-'),

                        Forms\Components\DatePicker::make('snapshot_date')
                            ->label('Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->required()
                            ->live()
                            ->maxDate(now())
                            ->afterStateUpdated(function (?string $state): void {
                                if ($state) {
                                    $this->data['balances'] = $this->defaultBalancesForDate($state);
                                }
                            }),

                        ...$accountFields,

                        Forms\Components\Placeholder::make('total_preview')
                            ->label('Total Saldo')
                            ->content(fn (Get $get): string => 'Rp ' . number_format(
                                $this->calculateTotalFromState($get('balances') ?? []),
                                0,
                                ',',
                                '.'
                            ))
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('untung_preview')
                            ->label('Perkiraan Untung Hari Ini')
                            ->content(function (Get $get): string {
                                $date = $get('snapshot_date') ?? now()->format('Y-m-d');
                                $total = $this->calculateTotalFromState($get('balances') ?? []);
                                $branchId = AccessControl::userBranchId();
                                $kemarin = $branchId
                                    ? BrilinkSnapshotService::previousTotal($branchId, $date)
                                    : 0;
                                $untung = $total - $kemarin;

                                $missedDays = $branchId
                                    ? BrilinkSnapshotService::missedInputDays($branchId, $date)
                                    : null;

                                $gapNote = ($missedDays ?? 0) > 0
                                    ? sprintf(' · Lompat %d hari tanpa input', $missedDays)
                                    : '';

                                return sprintf(
                                    'Rp %s (Saldo kemarin: Rp %s)%s',
                                    number_format($untung, 0, ',', '.'),
                                    number_format($kemarin, 0, ',', '.'),
                                    $gapNote
                                );
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(ResponsiveForm::columns(2)),
            ])
            ->statePath('data');
    }

    protected function calculateTotalFromState(array $balances): float
    {
        $total = 0.0;

        foreach ($balances as $value) {
            $total += (float) (NominalInput::parse($value) ?? 0);
        }

        return $total;
    }

    public function simpan(): void
    {
        if (! $this->hasBrilinkAccounts()) {
            Notification::make()
                ->title('Belum ada akun Brilink')
                ->body('Hubungi pemilik untuk menambahkan akun Brilink di menu Administrasi → Akun.')
                ->warning()
                ->send();

            return;
        }

        $data = $this->form->getState();
        $parsedBalances = [];

        foreach ($data['balances'] ?? [] as $accountId => $value) {
            $parsedBalances[$accountId] = NominalInput::parse($value) ?? 0;
        }

        try {
            PeriodLockService::assertEditable($data['snapshot_date'], 'snapshot_date');

            $branchId = AccessControl::userBranchId();

            if ($branchId === null) {
                throw new \RuntimeException('Cabang tidak ditemukan.');
            }

            $snapshot = BrilinkSnapshotService::save(
                $branchId,
                auth()->id(),
                $data['snapshot_date'],
                $parsedBalances
            );

            $untung = BrilinkSnapshotService::profitForSnapshot($snapshot);
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Saldo Brilink berhasil disimpan')
            ->body(sprintf(
                'Total Rp %s · Untung Rp %s',
                number_format((float) $snapshot->total_balance, 0, ',', '.'),
                number_format($untung, 0, ',', '.')
            ))
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }
}
