<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Support\AccessControl;
use App\Support\RecordDateTime;
use App\Support\UpahKerjaServices;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpahKerjaRecent extends TableWidget
{
    protected static ?string $heading = 'Upah Kerja Terbaru';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return AccessControl::isBengkelPic();
    }

    public function table(Table $table): Table
    {
        $categoryId = TransactionCategory::findBySlug('upah_kerja')?->id;

        return $table
            ->query(
                AccessControl::scopeTransactionsForUser(
                    Transaction::query()
                        ->when($categoryId, fn ($query) => $query->where('transaction_category_id', $categoryId))
                        ->latest()
                )->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal & Jam')
                    ->formatStateUsing(fn (Transaction $record): string => RecordDateTime::forTransaction($record)),

                Tables\Columns\TextColumn::make('worker.name')
                    ->label('Pekerja')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Jasa')
                    ->formatStateUsing(fn (?string $state): string => $state ? UpahKerjaServices::label($state) : '-')
                    ->hiddenFrom('md'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', true),
            ])
            ->paginated(false);
    }
}
