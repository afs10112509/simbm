<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceTechnicianResource\Pages;
use App\Models\ServiceTechnician;
use App\Support\AccessControl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceTechnicianResource extends Resource
{
    protected static ?string $model = ServiceTechnician::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    protected static ?string $navigationGroup = 'Konter';

    protected static ?string $navigationLabel = 'Data Tukang Service';

    protected static ?string $modelLabel = 'Tukang Service';

    protected static ?string $pluralModelLabel = 'Data Tukang Service';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return AccessControl::canViewServiceTechnicians();
    }

    public static function canCreate(): bool
    {
        return AccessControl::canManageServiceTechnicians();
    }

    public static function canEdit($record): bool
    {
        return AccessControl::canManageServiceTechnicians()
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
                    ->label('Nama Tukang Service')
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
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->visible(fn () => AccessControl::canViewAllBranches()),

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
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => AccessControl::canManageServiceTechnicians()),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => AccessControl::canManageServiceTechnicians()),
            ])
            ->emptyStateHeading('Belum ada tukang service')
            ->emptyStateDescription('Tambahkan data tukang terlebih dahulu sebelum input service.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceTechnicians::route('/'),
            'create' => Pages\CreateServiceTechnician::route('/create'),
            'edit' => Pages\EditServiceTechnician::route('/{record}/edit'),
        ];
    }
}
