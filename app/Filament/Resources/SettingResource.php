<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use App\Support\AccessControl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $navigationLabel = 'Pengaturan Aplikasi';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'Pengaturan';

    protected static ?string $pluralModelLabel = 'Pengaturan Aplikasi';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function canViewAny(): bool
    {
        return AccessControl::isOwner();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getNavigationUrl(): string
    {
        $setting = Setting::query()->firstOrCreate([]);

        return static::getUrl('edit', [
            'record' => $setting,
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('app_name')
                    ->label('Nama Aplikasi'),

                Forms\Components\TextInput::make('company_name')
                    ->label('Nama Perusahaan'),

                Forms\Components\Textarea::make('address')
                    ->label('Alamat'),

                Forms\Components\TextInput::make('phone')
                    ->label('Telepon'),

                Forms\Components\TextInput::make('email')
                    ->email(),

                Forms\Components\TextInput::make('currency')
                    ->default('IDR'),

                Forms\Components\FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->disk('public')
                    ->directory('logos')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->helperText('Format PNG/JPG, maks. 2 MB. Tampil di halaman login dan sidebar.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
