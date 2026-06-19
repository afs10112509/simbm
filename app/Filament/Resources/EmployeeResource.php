<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Branch;
use App\Models\Employee;
use App\Support\AccessControl;
use App\Support\EmployeeNumberGenerator;
use App\Support\GenderOptions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $navigationLabel = 'Data Karyawan';

    protected static ?string $modelLabel = 'Karyawan';

    protected static ?string $pluralModelLabel = 'Data Karyawan';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return AccessControl::canManageEmployees();
    }

    public static function canCreate(): bool
    {
        return AccessControl::canManageEmployees();
    }

    public static function canEdit($record): bool
    {
        return AccessControl::canManageEmployees();
    }

    public static function canDelete($record): bool
    {
        return AccessControl::canManageEmployees();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pribadi')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->options(fn () => Branch::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\Placeholder::make('employee_number_preview')
                            ->label('Nomor Induk Karyawan')
                            ->content(fn (?Employee $record): string => $record?->employee_number
                                ?? EmployeeNumberGenerator::next() . ' (otomatis saat disimpan)')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('full_name')
                            ->label('Nama Lengkap (sesuai KTP)')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('phone')
                            ->label('WhatsApp / HP')
                            ->tel()
                            ->required()
                            ->maxLength(30),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('identity_number')
                            ->label('NIK (KTP)')
                            ->required()
                            ->length(16)
                            ->numeric()
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options(GenderOptions::labels())
                            ->required(),

                        Forms\Components\TextInput::make('birth_place')
                            ->label('Tempat Lahir')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Tanggal Lahir')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required()
                            ->maxDate(now()),

                        Forms\Components\Textarea::make('address')
                            ->label('Alamat Domisili Lengkap')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Pekerjaan')
                    ->schema([
                        Forms\Components\TextInput::make('position')
                            ->label('Jabatan')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('joined_at')
                            ->label('Tanggal Bergabung')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required()
                            ->maxDate(now()),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Data Administratif & Gaji')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('bank_account_number')
                            ->label('Nomor Rekening')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('bank_account_holder')
                            ->label('Nama Pemilik Rekening')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('tax_id')
                            ->label('NPWP')
                            ->maxLength(30),

                        Forms\Components\TextInput::make('bpjs_health_number')
                            ->label('BPJS Kesehatan')
                            ->maxLength(30),

                        Forms\Components\TextInput::make('bpjs_employment_number')
                            ->label('BPJS Ketenagakerjaan')
                            ->maxLength(30)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Forms\Components\Section::make('Pendidikan & Kontak Darurat')
                    ->schema([
                        Forms\Components\TextInput::make('last_education')
                            ->label('Pendidikan Terakhir')
                            ->placeholder('SMA, D3, S1, ...')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('education_major')
                            ->label('Gelar / Jurusan')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('emergency_contact_name')
                            ->label('Nama Kontak Darurat')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->label('Telepon Darurat')
                            ->tel()
                            ->maxLength(30),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_number')
                    ->label('NI Karyawan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->searchable(),

                Tables\Columns\TextColumn::make('joined_at')
                    ->label('Bergabung')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('employee_number')
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Cabang')
                    ->options(fn () => Branch::query()->orderBy('name')->pluck('name', 'id')),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('Belum ada data karyawan')
            ->emptyStateDescription('Tambahkan data karyawan untuk seluruh cabang.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
