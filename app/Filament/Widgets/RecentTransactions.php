<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Support\AccessControl;
use App\Support\RecordDateTime;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactions extends BaseWidget
{
    protected static ?string $heading = 'Transaksi Terbaru';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return AccessControl::canViewDashboardWidgets();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AccessControl::scopeTransactionsForUser(
                    Transaction::query()->forLedgerReport()->latest()
                )->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal & Jam')
                    ->formatStateUsing(fn (Transaction $record): string => RecordDateTime::forTransaction($record)),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', true),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'income' => 'Pemasukan',
                        'expense' => 'Pengeluaran',
                    })
                    ->colors([
                        'success' => 'income',
                        'danger' => 'expense',
                    ]),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->hiddenFrom('md'),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->hiddenFrom('lg'),
            ])
            ->paginated(false);
    }
}
