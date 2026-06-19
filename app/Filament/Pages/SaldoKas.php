<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\AccountReconciliation;
use App\Support\AccessControl;
use App\Support\AccountBalanceSummary;
use App\Support\AccountPurpose;
use App\Support\NominalInput;
use App\Services\AccountReconciliationService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SaldoKas extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Saldo Kas';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.saldo-kas';

    public static function canAccess(): bool
    {
        return AccessControl::picHasBranch() || AccessControl::isOwner();
    }

    public function getTitle(): string
    {
        return 'Saldo Kas';
    }

    public function getBranchName(): string
    {
        if (AccessControl::isOwner()) {
            return 'Semua cabang (ringkasan akun Anda)';
        }

        return auth()->user()->branch?->name ?? '-';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAccountGroups(): array
    {
        $branchId = AccessControl::canViewAllBranches()
            ? null
            : AccessControl::userBranchId();

        $purposes = AccessControl::isBengkelPic()
            ? [AccountPurpose::GENERAL]
            : [
                AccountPurpose::GENERAL,
                AccountPurpose::BRILINK,
                AccountPurpose::SERVICE,
            ];

        if (AccessControl::isOwner()) {
            $purposes = [
                AccountPurpose::GENERAL,
                AccountPurpose::BRILINK,
                AccountPurpose::SERVICE,
                AccountPurpose::UPAH_KERJA,
            ];
        }

        $groups = [];

        foreach ($purposes as $purpose) {
            $accounts = $branchId
                ? AccountBalanceSummary::accountsForPurpose($branchId, $purpose)
                : Account::query()->forPurpose($purpose)->active()->with('branch')->orderBy('branch_id')->orderBy('name')->get();

            if ($accounts->isEmpty()) {
                continue;
            }

            $groups[] = [
                'purpose' => $purpose,
                'label' => AccountPurpose::label($purpose),
                'accounts' => $accounts,
                'total' => (float) $accounts->sum('balance'),
            ];
        }

        return $groups;
    }

    public function getGrandTotal(): float
    {
        return (float) collect($this->getAccountGroups())->sum('total');
    }

    /**
     * @return \Illuminate\Support\Collection<int, AccountReconciliation>
     */
    public function getRecentReconciliations()
    {
        $query = AccountReconciliation::query()
            ->with(['account.branch', 'user'])
            ->latest('reconciled_at')
            ->limit(10);

        if (! AccessControl::canViewAllBranches()) {
            $query->whereHas('account', fn ($accountQuery) => $accountQuery->where('branch_id', AccessControl::userBranchId()));
        }

        return $query->get();
    }

    protected function getHeaderActions(): array
    {
        if (! AccessControl::isOwner()) {
            return [];
        }

        return [
            Action::make('reconcile')
                ->label('Rekonsiliasi Saldo')
                ->icon('heroicon-o-scale')
                ->form([
                    Forms\Components\Select::make('account_id')
                        ->label('Akun')
                        ->options(fn () => Account::query()
                            ->active()
                            ->with('branch')
                            ->orderBy('branch_id')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (Account $account) => [
                                $account->id => sprintf(
                                    '%s — %s (sistem: Rp %s)',
                                    $account->branch?->name,
                                    $account->name,
                                    number_format((float) $account->balance, 0, ',', '.')
                                ),
                            ]))
                        ->searchable()
                        ->required()
                        ->live(),

                    Forms\Components\Placeholder::make('system_balance')
                        ->label('Saldo Sistem')
                        ->content(function (Forms\Get $get): string {
                            $account = Account::find($get('account_id'));

                            return $account
                                ? 'Rp ' . number_format((float) $account->balance, 0, ',', '.')
                                : '-';
                        }),

                    NominalInput::make('physical_balance', 'Saldo Fisik / Rekening')
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $account = Account::query()->findOrFail($data['account_id']);

                    AccountReconciliationService::record(
                        $account,
                        (float) (NominalInput::parse($data['physical_balance'] ?? null) ?? 0),
                        $data['notes'] ?? null,
                    );

                    Notification::make()
                        ->title('Rekonsiliasi tersimpan')
                        ->body('Saldo akun disesuaikan jika ada selisih.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
