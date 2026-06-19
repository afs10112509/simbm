<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Branch;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Support\AccessControl;
use App\Support\NominalInput;
use App\Support\RecordDateTime;
use App\Support\ResponsiveForm;
use App\Support\UpahKerjaServices;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Transaksi';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?string $pluralModelLabel = 'Transaksi';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        if (AccessControl::isPic()) {
            return 'Riwayat Transaksi';
        }

        return static::$navigationLabel;
    }

    public static function getNavigationGroup(): ?string
    {
        return static::$navigationGroup;
    }

    public static function getNavigationSort(): ?int
    {
        return static::$navigationSort;
    }

    public static function canViewAny(): bool
    {
        return AccessControl::canViewTransactions();
    }

    public static function canCreate(): bool
    {
        return AccessControl::canCreateTransaction();
    }

    public static function canEdit($record): bool
    {
        return AccessControl::canEditTransaction($record);
    }

    public static function canDelete($record): bool
    {
        return AccessControl::canDeleteTransaction($record);
    }

    public static function getEloquentQuery(): Builder
    {
        return AccessControl::scopeTransactionsForUser(parent::getEloquentQuery());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),

                Forms\Components\Select::make('branch_id')
                    ->label('Cabang')
                    ->relationship('branch', 'name')
                    ->default(auth()->user()->branch_id)
                    ->disabled(fn () => ! AccessControl::canViewAllBranches())
                    ->dehydrated()
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('account_id')
                    ->label('Akun')
                    ->relationship(
                        'account',
                        'name',
                        fn ($query, callable $get) => $query->where('branch_id', $get('branch_id'))
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('type')
                    ->label('Jenis Transaksi')
                    ->options([
                        'income' => 'Pemasukan',
                        'expense' => 'Pengeluaran',
                    ])
                    ->live()
                    ->required(),

                Forms\Components\Select::make('transaction_category_id')
                    ->label('Kategori')
                    ->relationship(
                        'category',
                        'name',
                        function ($query, callable $get) {
                            $branch = Branch::find($get('branch_id') ?? auth()->user()->branch_id);

                            if ($branch) {
                                $query->availableForPic($branch);
                            }

                            $query->where('type', $get('type'));
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                NominalInput::make('amount')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Keterangan')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->default(now())
                    ->required(),
            ])
            ->columns(ResponsiveForm::columns(2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal & Jam')
                    ->formatStateUsing(fn (Transaction $record): string => RecordDateTime::forTransaction($record))
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', true)
                    ->color(fn ($record) => $record->type === 'income' ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'income' => 'Pemasukan',
                        'expense' => 'Pengeluaran',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'income',
                        'danger' => 'expense',
                    ]),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->hiddenFrom('md'),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => AccessControl::canViewAllBranches())
                    ->hiddenFrom('lg'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Input Oleh')
                    ->sortable()
                    ->hiddenFrom('lg'),

                Tables\Columns\TextColumn::make('account.name')
                    ->label('Akun')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->hiddenFrom('md'),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Jasa')
                    ->formatStateUsing(fn (?string $state): string => $state ? UpahKerjaServices::label($state) : '-')
                    ->visible(fn () => AccessControl::isBengkelPic() || AccessControl::isOwner())
                    ->toggleable(),

                Tables\Columns\TextColumn::make('worker.name')
                    ->label('Pekerja')
                    ->placeholder('-')
                    ->visible(fn () => AccessControl::isBengkelPic() || AccessControl::isOwner())
                    ->toggleable()
                    ->hiddenFrom('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'income' => 'Pemasukan',
                        'expense' => 'Pengeluaran',
                    ]),

                Tables\Filters\SelectFilter::make('transaction_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Cabang')
                    ->relationship('branch', 'name')
                    ->visible(fn () => AccessControl::canViewAllBranches()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Transaction $record) => AccessControl::canEditTransaction($record)),

                Tables\Actions\ViewAction::make()
                    ->visible(fn () => AccessControl::isOwner()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => AccessControl::picHasBranch()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
