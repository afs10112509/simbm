<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\ServiceTechnician;
use App\Services\PeriodLockService;
use App\Services\ServiceRecordService;
use App\Support\AccessControl;
use App\Support\AccountBalanceSummary;
use App\Support\AccountPurpose;
use App\Support\NominalInput;
use App\Support\ResponsiveForm;
use App\Support\ServiceDamageTypes;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ServiceInput extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Input Service';

    protected static ?string $navigationGroup = 'Konter';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.service-input';

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Input Service';
    }

    public static function canAccess(): bool
    {
        return AccessControl::canManageService()
            && AccessControl::userBranchId() !== null;
    }

    public function hasTechnicians(): bool
    {
        $branchId = AccessControl::userBranchId();

        if ($branchId === null) {
            return false;
        }

        return ServiceTechnician::query()
            ->forBranch($branchId)
            ->active()
            ->exists();
    }

    public function mount(): void
    {
        $branchId = AccessControl::userBranchId();

        if ($branchId === null) {
            return;
        }

        $this->form->fill([
            'account_id' => AccountBalanceSummary::accountsForPurpose(
                $branchId,
                AccountPurpose::SERVICE
            )->first()?->id,
            'items' => [
                [
                    'service_date' => now()->format('Y-m-d'),
                    'service_technician_id' => null,
                    'device_brand' => '',
                    'device_type' => '',
                    'damage_type' => null,
                    'modal' => null,
                    'price' => null,
                ],
            ],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi')
                    ->columns(ResponsiveForm::columns(2))
                    ->schema([
                        Forms\Components\Placeholder::make('branch_info')
                            ->label('Cabang')
                            ->content(fn () => auth()->user()->branch?->name ?? '-'),

                        Forms\Components\Select::make('account_id')
                            ->label('Akun Service')
                            ->options(fn () => AccountBalanceSummary::accountsForPurpose(
                                AccessControl::userBranchId(),
                                AccountPurpose::SERVICE
                            )->mapWithKeys(fn (Account $account) => [
                                $account->id => sprintf(
                                    '%s — Rp %s',
                                    $account->name,
                                    number_format((float) $account->balance, 0, ',', '.')
                                ),
                            ]))
                            ->searchable()
                            ->required(),
                    ]),

                Forms\Components\Repeater::make('items')
                    ->label('Daftar Service')
                    ->defaultItems(1)
                    ->addActionLabel('Tambah Baris')
                    ->reorderable(false)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(ResponsiveForm::columns(4))
                            ->schema([
                                Forms\Components\DatePicker::make('service_date')
                                    ->label('Tanggal')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\Select::make('service_technician_id')
                                    ->label('Tukang Service')
                                    ->options(fn () => ServiceTechnician::query()
                                        ->forBranch(AccessControl::userBranchId())
                                        ->active()
                                        ->orderBy('name')
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                Forms\Components\TextInput::make('device_brand')
                                    ->label('Merek')
                                    ->required()
                                    ->placeholder('OPPO, SAMSUNG, ...'),

                                Forms\Components\TextInput::make('device_type')
                                    ->label('Type')
                                    ->required()
                                    ->placeholder('A57, HOT 40, ...'),

                                Forms\Components\Select::make('damage_type')
                                    ->label('Kerusakan')
                                    ->options(ServiceDamageTypes::labels())
                                    ->searchable()
                                    ->required(),

                                NominalInput::make('modal')
                                    ->label('Modal'),

                                NominalInput::make('price')
                                    ->label('Harga')
                                    ->required(),

                                Forms\Components\Placeholder::make('profit_preview')
                                    ->label('Total (Laba)')
                                    ->content(function (Get $get): string {
                                        $price = NominalInput::parse($get('price') ?? 0);
                                        $modal = NominalInput::parse($get('modal') ?? 0);

                                        return 'Rp ' . number_format(max($price - $modal, 0), 0, ',', '.');
                                    }),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function simpan(): void
    {
        if (! $this->hasTechnicians()) {
            Notification::make()
                ->title('Belum ada tukang service')
                ->body('Tambahkan data tukang di menu Data Tukang Service terlebih dahulu.')
                ->warning()
                ->send();

            return;
        }

        $data = $this->form->getState();
        $branchId = AccessControl::userBranchId();

        if ($branchId === null) {
            Notification::make()
                ->title('Cabang tidak ditemukan')
                ->body('Akun Anda belum terhubung ke cabang konter.')
                ->danger()
                ->send();

            return;
        }

        $validTechnicianIds = ServiceTechnician::query()
            ->forBranch($branchId)
            ->active()
            ->pluck('id')
            ->all();

        $items = $data['items'] ?? [];

        if ($items === []) {
            Notification::make()
                ->title('Tidak ada data')
                ->body('Tambahkan minimal satu baris service.')
                ->warning()
                ->send();

            return;
        }

        $savedCount = 0;

        try {
            DB::transaction(function () use ($items, $validTechnicianIds, $branchId, $data, &$savedCount) {
                foreach ($items as $index => $item) {
                    $rowNumber = $index + 1;

                    if (empty($item['service_technician_id'])) {
                        throw new \RuntimeException("Baris {$rowNumber}: tukang service wajib dipilih.");
                    }

                    if (! in_array((int) $item['service_technician_id'], $validTechnicianIds, true)) {
                        throw new \RuntimeException("Baris {$rowNumber}: tukang service tidak valid untuk cabang ini.");
                    }

                    if (blank($item['device_brand'] ?? null) || blank($item['device_type'] ?? null)) {
                        throw new \RuntimeException("Baris {$rowNumber}: merek dan type wajib diisi.");
                    }

                    if (blank($item['damage_type'] ?? null)) {
                        throw new \RuntimeException("Baris {$rowNumber}: kerusakan wajib dipilih.");
                    }

                    PeriodLockService::assertEditable($item['service_date'] ?? null, 'service_date');

                    if (blank(NominalInput::parse($item['price'] ?? null))) {
                        throw new \RuntimeException("Baris {$rowNumber}: harga wajib diisi.");
                    }

                    $technician = ServiceTechnician::find($item['service_technician_id']);

                    ServiceRecordService::create([
                        'branch_id' => $branchId,
                        'account_id' => $data['account_id'],
                        'service_technician_id' => $item['service_technician_id'],
                        'user_id' => auth()->id(),
                        'service_date' => $item['service_date'],
                        'device_brand' => strtoupper(trim($item['device_brand'])),
                        'device_type' => strtoupper(trim($item['device_type'])),
                        'damage_type' => $item['damage_type'],
                        'modal' => NominalInput::parse($item['modal'] ?? 0),
                        'price' => NominalInput::parse($item['price']),
                        'technician_name' => $technician?->name,
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
            ->body("{$savedCount} data service berhasil disimpan.")
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }
}
