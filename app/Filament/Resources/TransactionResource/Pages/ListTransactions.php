<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\TransactionCategory;
use App\Support\AccessControl;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    public function mount(): void
    {
        parent::mount();

        if (! AccessControl::isBengkelPic()) {
            return;
        }

        $categoryId = TransactionCategory::findBySlug('upah_kerja')?->id;

        if ($categoryId === null) {
            return;
        }

        $this->tableFilters['transaction_category_id']['value'] ??= $categoryId;
    }

    public function getTitle(): string
    {
        if (AccessControl::isPic()) {
            return 'Riwayat Transaksi';
        }

        return 'Daftar Transaksi';
    }

    protected function getHeaderActions(): array
    {
        if (! TransactionResource::canCreate()) {
            return [];
        }

        return [
            Actions\CreateAction::make(),
        ];
    }
}
