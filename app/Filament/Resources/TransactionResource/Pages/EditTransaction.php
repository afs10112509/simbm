<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Support\AccessControl;
use App\Support\AccountBalanceValidator;
use App\Support\NominalInput;
use App\Services\PeriodLockService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        return $record
            ? AccessControl::canEditTransaction($record)
            : parent::canAccess($parameters);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => AccessControl::canDeleteTransaction($this->record)),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        PeriodLockService::assertEditable($data['transaction_date'], 'transaction_date');

        AccountBalanceValidator::assertTransactionUpdateAllowed(
            $this->record,
            (int) $data['account_id'],
            $data['type'],
            (float) (NominalInput::parse($data['amount'] ?? null) ?? 0),
        );

        return $data;
    }
}
