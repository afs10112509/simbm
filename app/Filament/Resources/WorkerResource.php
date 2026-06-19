<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkerResource\Pages;
use App\Models\Worker;
use App\Support\AccessControl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkerResource extends Resource
{
    protected static ?string $model = Worker::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Bengkel';

    protected static ?string $navigationLabel = 'Data Pekerja';

    protected static ?string $modelLabel = 'Pekerja';

    protected static ?string $pluralModelLabel = 'Data Pekerja';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return AccessControl::canViewWorkers();
    }

    public static function canCreate(): bool
    {
        return AccessControl::canManageWorkers();
    }

    public static function canEdit($record): bool
    {
        return AccessControl::canManageWorkers()
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

                Forms\Components\TextInput::make('name')
                    ->label('Nama Pekerja')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->maxLength(30),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => AccessControl::canManageWorkers()),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => AccessControl::canManageWorkers()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => AccessControl::canManageWorkers()),
            ])
            ->emptyStateHeading('Belum ada pekerja')
            ->emptyStateDescription('Tambahkan data pekerja terlebih dahulu sebelum input upah kerja.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkers::route('/'),
            'create' => Pages\CreateWorker::route('/create'),
            'edit' => Pages\EditWorker::route('/{record}/edit'),
        ];
    }
}
