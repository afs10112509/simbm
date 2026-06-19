<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Branch;
use App\Models\User;
use App\Support\AccessControl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Administrasi';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return AccessControl::canManageUsers();
    }

    public static function canCreate(): bool
    {
        return AccessControl::canManageUsers();
    }

    public static function canEdit($record): bool
    {
        return AccessControl::canManageUsers();
    }

    public static function canDelete($record): bool
    {
        return AccessControl::canManageUsers();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required(),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    ->required(fn ($record) => ! $record)
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state)),

                Forms\Components\Select::make('role')
                    ->label('Peran')
                    ->options(AccessControl::roleLabels())
                    ->required()
                    ->live()
                    ->default(fn (?User $record) => $record?->roles->first()?->name),

                Forms\Components\Select::make('branch_id')
                    ->label('Cabang')
                    ->options(fn () => Branch::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn (Get $get) => AccessControl::roleRequiresBranch($get('role') ?? ''))
                    ->required(fn (Get $get) => AccessControl::roleRequiresBranch($get('role') ?? '')),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('roles.name')
                    ->label('Peran')
                    ->formatStateUsing(fn (string $state): string => AccessControl::roleLabels()[$state] ?? $state)
                    ->colors([
                        'success' => AccessControl::ROLE_OWNER,
                        'primary' => AccessControl::ROLE_PIC,
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function validateRoleAndBranch(?string $role, ?int $branchId): void
    {
        if (! $role) {
            throw ValidationException::withMessages([
                'role' => 'Peran wajib dipilih.',
            ]);
        }

        if (! AccessControl::roleRequiresBranch($role)) {
            return;
        }

        if (! $branchId) {
            throw ValidationException::withMessages([
                'branch_id' => 'Cabang wajib dipilih untuk peran ini.',
            ]);
        }

        $branch = Branch::find($branchId);

        if (! AccessControl::validateRoleForBranch($role, $branch)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Peran ini tidak sesuai dengan tipe cabang yang dipilih.',
            ]);
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
