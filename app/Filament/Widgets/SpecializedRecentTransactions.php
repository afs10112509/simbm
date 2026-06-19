<?php

namespace App\Filament\Widgets;

use App\Models\BrilinkDailySnapshot;
use App\Services\BrilinkSnapshotService;
use App\Support\AccessControl;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class SpecializedRecentTransactions extends TableWidget
{
    protected static ?string $heading = 'Saldo Brilink Terbaru';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return AccessControl::isKonterPic();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BrilinkDailySnapshot::query()
                    ->where('branch_id', auth()->user()->branch_id)
                    ->latest('snapshot_date')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('snapshot_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_balance')
                    ->label('Total Saldo')
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('untung')
                    ->label('Untung')
                    ->state(fn (BrilinkDailySnapshot $record): float => BrilinkSnapshotService::profitForSnapshot($record))
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->color(fn ($state): string => (float) $state >= 0 ? 'success' : 'danger'),
            ])
            ->paginated(false);
    }
}
