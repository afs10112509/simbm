<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use App\Support\AccessControl;
use App\Support\AccountPurpose;
use App\Support\NominalInput;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationLabel = 'Akun';

    protected static ?string $modelLabel = 'Akun';

    protected static ?string $pluralModelLabel = 'Akun';

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return AccessControl::canManageMasterData();
    }

    public static function canCreate(): bool
    {
        return AccessControl::canManageMasterData();
    }

    public static function canEdit($record): bool
    {
        return AccessControl::canManageMasterData();
    }

    public static function canDelete($record): bool
    {
        return AccessControl::canManageMasterData();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->label('Cabang')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('Nama Akun')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'cash' => 'Tunai',
                        'bank' => 'Bank',
                        'ewallet' => 'Dompet Digital',
                    ])
                    ->required(),

                Forms\Components\Select::make('purpose')
                    ->label('Keperluan Akun')
                    ->options(AccountPurpose::labels())
                    ->default(AccountPurpose::GENERAL)
                    ->required()
                    ->helperText('Pisahkan akun Brilink, Service, Upah Kerja, dan umum PIC'),

                Forms\Components\Placeholder::make('balance_history_warning')
                    ->label('')
                    ->content('Akun ini memiliki riwayat transaksi, transfer, atau input Brilink. Ubah saldo manual hanya untuk rekonsiliasi.')
                    ->visible(fn (?Account $record): bool => $record?->hasBalanceHistory() ?? false)
                    ->columnSpanFull(),

                NominalInput::make('balance', 'Saldo')
                    ->default('0')
                    ->required(),

                Forms\Components\Toggle::make('confirm_manual_balance')
                    ->label('Saya yakin ingin mengubah saldo manual')
                    ->helperText('Wajib dicentang jika saldo diubah pada akun yang sudah memiliki riwayat.')
                    ->visible(fn (?Account $record): bool => $record?->hasBalanceHistory() ?? false)
                    ->dehydrated(false)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Akun')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'bank' => 'Bank',
                        'ewallet' => 'Dompet Digital',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->formatStateUsing(fn (string $state): string => AccountPurpose::label($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        AccountPurpose::GENERAL => 'gray',
                        AccountPurpose::BRILINK => 'info',
                        AccountPurpose::SERVICE => 'warning',
                        AccountPurpose::UPAH_KERJA => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo')
                    ->money('IDR'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}