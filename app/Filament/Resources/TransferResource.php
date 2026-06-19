<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferResource\Pages;
use App\Models\Transfer;
use App\Support\AccessControl;
use App\Support\AccountPurpose;
use App\Support\NominalInput;
use App\Support\RecordDateTime;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Transfer Antar Akun';

    protected static ?string $modelLabel = 'Transfer';

    protected static ?string $pluralModelLabel = 'Transfer Antar Akun';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return AccessControl::isOwner() || AccessControl::isPic();
    }

    public static function canCreate(): bool
    {
        return AccessControl::canManageTransfers();
    }

    public static function canEdit($record): bool
    {
        return AccessControl::canManageTransfers()
            && AccessControl::userOwnsBranchRecord($record);
    }

    public static function canDelete($record): bool
    {
        return self::canEdit($record);
    }

    public static function getEloquentQuery(): Builder
    {
        return AccessControl::scopeToUserBranch(parent::getEloquentQuery());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('branch_id')
                    ->default(auth()->user()->branch_id),

                Forms\Components\Placeholder::make('branch_name')
                    ->label('Cabang')
                    ->content(fn () => auth()->user()->branch?->name ?? '-'),

                Forms\Components\Select::make('from_account_id')
                    ->label('Dari Akun')
                    ->options(function (Get $get) {
                        $branchId = $get('branch_id') ?? auth()->user()->branch_id;
                        $query = \App\Models\Account::query()
                            ->where('branch_id', $branchId)
                            ->active();

                        if (AccessControl::isPic()) {
                            $query->forPurpose(AccountPurpose::GENERAL);
                        }

                        return $query->pluck('name', 'id');
                    })
                    ->searchable()
                    ->live()
                    ->required(),

                Forms\Components\Select::make('to_account_id')
                    ->label('Ke Akun')
                    ->options(function (Get $get) {
                        $branchId = $get('branch_id') ?? auth()->user()->branch_id;
                        $fromAccountId = $get('from_account_id');

                        $query = \App\Models\Account::query()
                            ->where('branch_id', $branchId)
                            ->active();

                        if (AccessControl::isPic()) {
                            $query->forPurpose(AccountPurpose::GENERAL);
                        } elseif ($fromAccountId) {
                            $fromPurpose = \App\Models\Account::query()
                                ->whereKey($fromAccountId)
                                ->value('purpose');

                            if ($fromPurpose) {
                                $query->forPurpose($fromPurpose);
                            }
                        }

                        return $query->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->different('from_account_id')
                    ->helperText('Transfer hanya antar akun dengan keperluan yang sama'),

                NominalInput::make('amount')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Keterangan')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('transfer_date')
                    ->label('Tanggal Transfer')
                    ->default(now())
                    ->required(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable(),

                Tables\Columns\TextColumn::make('fromAccount.name')
                    ->label('Dari Akun'),

                Tables\Columns\TextColumn::make('toAccount.name')
                    ->label('Ke Akun'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal & Jam')
                    ->formatStateUsing(fn ($record): string => RecordDateTime::forTransfer($record))
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => AccessControl::canManageTransfers()),

                Tables\Actions\ViewAction::make()
                    ->visible(fn () => AccessControl::isOwner()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => AccessControl::canManageTransfers()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransfers::route('/'),
            'create' => Pages\CreateTransfer::route('/create'),
            'edit' => Pages\EditTransfer::route('/{record}/edit'),
        ];
    }
}
