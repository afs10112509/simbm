<?php

namespace App\Filament\Resources\TransferResource\Pages;

use App\Filament\Resources\TransferResource;
use App\Models\Account;
use App\Support\AccountBalanceValidator;
use App\Support\NominalInput;
use App\Services\PeriodLockService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditTransfer extends EditRecord
{
    protected static string $resource = TransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        PeriodLockService::assertEditable($data['transfer_date'] ?? now()->toDateString(), 'transfer_date');

        $this->assertSameAccountPurpose(
            (int) $data['from_account_id'],
            (int) $data['to_account_id']
        );

        AccountBalanceValidator::assertTransferAllowed(
            $this->record,
            (int) $data['from_account_id'],
            (float) (NominalInput::parse($data['amount'] ?? null) ?? 0),
        );

        return $data;
    }

    protected function assertSameAccountPurpose(int $fromAccountId, int $toAccountId): void
    {
        $fromPurpose = Account::query()->whereKey($fromAccountId)->value('purpose');
        $toPurpose = Account::query()->whereKey($toAccountId)->value('purpose');

        if ($fromPurpose !== $toPurpose) {
            Notification::make()
                ->title('Transfer ditolak')
                ->body('Saldo Brilink, Service, Upah Kerja, dan umum PIC tidak boleh dicampur.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'to_account_id' => 'Pilih akun dengan keperluan yang sama.',
            ]);
        }
    }
}
