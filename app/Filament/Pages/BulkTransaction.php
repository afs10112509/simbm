<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Support\AccountBalanceSummary;
use App\Support\AccountBalanceValidator;
use App\Support\AccountPurpose;
use App\Services\PeriodLockService;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Support\AccessControl;
use App\Support\NominalInput;
use App\Support\ResponsiveForm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class BulkTransaction extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Input Kas Harian';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.bulk-transaction';

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Input Kas Harian';
    }

    public static function canAccess(): bool
    {
        return AccessControl::canInputFinancialTracker();
    }

    public function mount(): void
    {
        $branchId = AccessControl::userBranchId();

        if ($branchId === null) {
            return;
        }

        $this->form->fill([
            'branch_id' => $branchId,
            'transaction_date' => now()->format('Y-m-d'),
            'transactions' => [
                [
                    'type' => 'expense',
                    'amount' => null,
                    'description' => '',
                ],
            ],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->columns(ResponsiveForm::columns(3))
                    ->schema([
                        Forms\Components\Placeholder::make('branch_name')
                            ->label('Cabang')
                            ->content(fn () => auth()->user()->branch?->name ?? '-'),

                        Forms\Components\Hidden::make('branch_id')
                            ->default(auth()->user()->branch_id),

                        Forms\Components\Select::make('account_id')
                            ->label('Akun')
                            ->options(function () {
                                return AccountBalanceSummary::accountsForPurpose(
                                    auth()->user()->branch_id,
                                    AccountPurpose::GENERAL
                                )->mapWithKeys(fn (Account $account) => [
                                    $account->id => sprintf(
                                        '%s — Rp %s',
                                        $account->name,
                                        number_format((float) $account->balance, 0, ',', '.')
                                    ),
                                ]);
                            })
                            ->helperText('Akun umum PIC / kas harian (terpisah dari Brilink, Service, Upah Kerja)')
                            ->searchable()
                            ->live()
                            ->required(),

                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Tanggal')
                            ->required(),
                    ]),

                Forms\Components\Repeater::make('transactions')
                    ->label('Daftar Transaksi')
                    ->defaultItems(1)
                    ->addActionLabel('Tambah Baris')
                    ->reorderable(false)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(ResponsiveForm::columns(4))
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Jenis')
                                    ->options([
                                        'income' => 'Pemasukan',
                                        'expense' => 'Pengeluaran',
                                    ])
                                    ->live()
                                    ->required(),

                                Forms\Components\Select::make('transaction_category_id')
                                    ->label('Kategori')
                                    ->options(function (callable $get) {
                                        if (! $get('type')) {
                                            return [];
                                        }

                                        $branch = auth()->user()->branch;

                                        if (! $branch) {
                                            return [];
                                        }

                                        return TransactionCategory::query()
                                            ->availableForPic($branch)
                                            ->where('type', $get('type'))
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required(),

                                NominalInput::make('amount')
                                    ->required(),

                                Forms\Components\TextInput::make('description')
                                    ->label('Keterangan'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function simpan(): void
    {
        $branchId = AccessControl::userBranchId();

        if ($branchId === null) {
            Notification::make()
                ->title('Cabang tidak ditemukan')
                ->body('Akun PIC belum terhubung ke cabang.')
                ->danger()
                ->send();

            return;
        }

        $validAccount = Account::query()
            ->where('id', $this->data['account_id'])
            ->where('branch_id', $branchId)
            ->forPic()
            ->active()
            ->exists();

        if (! $validAccount) {
            Notification::make()
                ->title('Akun tidak valid')
                ->body('Gunakan akun umum PIC, bukan akun Brilink/Service/Upah Kerja.')
                ->danger()
                ->send();

            return;
        }

        try {
            PeriodLockService::assertEditable($this->data['transaction_date'], 'transaction_date');

            AccountBalanceValidator::assertBatchTransactions(
                (int) $this->data['account_id'],
                $this->data['transactions'] ?? [],
            );
        } catch (\Illuminate\Validation\ValidationException $exception) {
            Notification::make()
                ->title('Saldo tidak mencukupi')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();

            return;
        }

        DB::transaction(function () use ($branchId) {
            foreach ($this->data['transactions'] as $item) {
                Transaction::create([
                    'branch_id' => $branchId,
                    'account_id' => $this->data['account_id'],
                    'transaction_category_id' => $item['transaction_category_id'],
                    'user_id' => auth()->id(),
                    'type' => $item['type'],
                    'amount' => NominalInput::parse($item['amount']),
                    'description' => $item['description'] ?? null,
                    'transaction_date' => $this->data['transaction_date'],
                ]);
            }
        });

        Notification::make()
            ->title('Berhasil')
            ->body('Semua transaksi berhasil disimpan.')
            ->success()
            ->send();

        $this->redirect(BulkTransaction::getUrl());
    }
}
