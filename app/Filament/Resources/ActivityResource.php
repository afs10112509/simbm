<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Support\AccessControl;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $navigationLabel = 'Aktivitas Pengguna';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'Aktivitas';

    protected static ?string $pluralModelLabel = 'Aktivitas Pengguna';

    /*
    |--------------------------------------------------------------------------
    | Hanya owner
    |--------------------------------------------------------------------------
    */

    public static function canViewAny(): bool
    {
        return AccessControl::isOwner();
    }

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('description')
                    ->label('Aktivitas')
                    ->searchable(),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Pengguna')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Modul')
                    ->formatStateUsing(
                        fn ($state) => match (class_basename($state)) {
                            'Transaction' => 'Transaksi',
                            'Transfer' => 'Transfer',
                            'Account' => 'Akun',
                            'Branch' => 'Cabang',
                            'User' => 'Pengguna',
                            'Setting' => 'Pengaturan',
                            'TransactionCategory' => 'Kategori Transaksi',
                            default => class_basename($state),
                        }
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since(),

            ])
            ->defaultSort('created_at', 'desc');
    }

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    */

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}