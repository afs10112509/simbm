<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\Worker;
use App\Support\AccessControl;
use App\Support\NominalInput;
use App\Support\ResponsiveForm;
use App\Support\UpahKerjaServices;
use App\Services\PeriodLockService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class UpahKerjaInput extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Input Upah Kerja';

    protected static ?string $navigationGroup = 'Bengkel';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.upah-kerja-input';

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Input Upah Kerja';
    }

    public static function canAccess(): bool
    {
        return AccessControl::canManageUpahKerja();
    }

    public function hasWorkers(): bool
    {
        $branchId = AccessControl::userBranchId();

        if ($branchId === null) {
            return false;
        }

        return Worker::query()
            ->forBranch($branchId)
            ->active()
            ->exists();
    }

    public function mount(): void
    {
        $this->form->fill([
            'items' => [
                [
                    'transaction_date' => now()->format('Y-m-d'),
                    'service_type' => null,
                    'worker_id' => null,
                    'amount' => null,
                ],
            ],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi')
                    ->schema([
                        Forms\Components\Placeholder::make('branch_info')
                            ->label('Cabang')
                            ->content(fn () => auth()->user()->branch?->name ?? '-'),

                        Forms\Components\Placeholder::make('info_saldo')
                            ->label('Catatan')
                            ->content('Upah kerja tidak mempengaruhi saldo kas. Lihat total di menu Laporan Upah Kerja.'),
                    ])
                    ->columns(ResponsiveForm::columns(2)),

                Forms\Components\Repeater::make('items')
                    ->label('Daftar Upah Kerja')
                    ->defaultItems(1)
                    ->addActionLabel('Tambah Baris')
                    ->reorderable(false)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(ResponsiveForm::columns(4))
                            ->schema([
                                Forms\Components\DatePicker::make('transaction_date')
                                    ->label('Tanggal')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\Select::make('service_type')
                                    ->label('Jasa')
                                    ->options(UpahKerjaServices::labels())
                                    ->searchable()
                                    ->required(),

                                Forms\Components\Select::make('worker_id')
                                    ->label('Pekerja')
                                    ->options(fn () => Worker::query()
                                        ->forBranch(auth()->user()->branch_id)
                                        ->active()
                                        ->orderBy('name')
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->helperText('Input pekerja dulu di menu Data Pekerja'),

                                NominalInput::make('amount')
                                    ->required(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function simpan(): void
    {
        if (! $this->hasWorkers()) {
            Notification::make()
                ->title('Belum ada pekerja')
                ->body('Tambahkan data pekerja di menu Data Pekerja terlebih dahulu.')
                ->warning()
                ->send();

            return;
        }

        $data = $this->form->getState();
        $category = TransactionCategory::findBySlug('upah_kerja');

        if (! $category) {
            Notification::make()
                ->title('Kategori tidak ditemukan')
                ->body('Hubungi pemilik untuk menyiapkan kategori upah kerja.')
                ->danger()
                ->send();

            return;
        }

        $branchId = AccessControl::userBranchId();

        if ($branchId === null) {
            Notification::make()
                ->title('Cabang tidak ditemukan')
                ->body('Akun Anda belum terhubung ke cabang bengkel.')
                ->danger()
                ->send();

            return;
        }

        $validWorkerIds = Worker::query()
            ->forBranch($branchId)
            ->active()
            ->pluck('id')
            ->all();

        $items = $data['items'] ?? [];

        if ($items === []) {
            Notification::make()
                ->title('Tidak ada data')
                ->body('Tambahkan minimal satu baris upah kerja.')
                ->warning()
                ->send();

            return;
        }

        $savedCount = 0;

        try {
            DB::transaction(function () use ($items, $validWorkerIds, $branchId, $category, &$savedCount) {
                foreach ($items as $index => $item) {
                    $rowNumber = $index + 1;

                    if (empty($item['worker_id'])) {
                        throw new \RuntimeException("Baris {$rowNumber}: pekerja wajib dipilih.");
                    }

                    if (! in_array((int) $item['worker_id'], $validWorkerIds, true)) {
                        throw new \RuntimeException("Baris {$rowNumber}: pekerja tidak valid untuk cabang ini.");
                    }

                    if (blank($item['service_type'] ?? null)) {
                        throw new \RuntimeException("Baris {$rowNumber}: jenis jasa wajib dipilih.");
                    }

                    PeriodLockService::assertEditable($item['transaction_date'] ?? null, 'transaction_date');

                    if (blank(NominalInput::parse($item['amount'] ?? null))) {
                        throw new \RuntimeException("Baris {$rowNumber}: nominal wajib diisi.");
                    }

                    $worker = Worker::find($item['worker_id']);
                    $serviceLabel = UpahKerjaServices::label($item['service_type']);

                    Transaction::create([
                        'branch_id' => $branchId,
                        'account_id' => null,
                        'transaction_category_id' => $category->id,
                        'worker_id' => $item['worker_id'],
                        'service_type' => $item['service_type'],
                        'user_id' => auth()->id(),
                        'type' => $category->type,
                        'amount' => NominalInput::parse($item['amount']),
                        'description' => $serviceLabel . ' — ' . ($worker?->name ?? '-'),
                        'transaction_date' => $item['transaction_date'],
                    ]);

                    $savedCount++;
                }
            });
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Berhasil')
            ->body("{$savedCount} data upah kerja berhasil disimpan.")
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }
}
