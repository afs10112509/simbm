<?php

namespace App\Filament\Resources\AccountResource\Pages;

use App\Filament\Resources\AccountResource;
use App\Support\NominalInput;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditAccount extends EditRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $newBalance = (float) (NominalInput::parse($data['balance'] ?? null) ?? 0);
        $oldBalance = (float) $this->record->balance;

        if (
            abs($newBalance - $oldBalance) >= 0.01
            && $this->record->hasBalanceHistory()
            && ! ($this->form->getState()['confirm_manual_balance'] ?? false)
        ) {
            Notification::make()
                ->title('Konfirmasi diperlukan')
                ->body('Akun ini memiliki riwayat transaksi. Centang konfirmasi jika Anda yakin ingin mengubah saldo manual.')
                ->warning()
                ->send();

            throw ValidationException::withMessages([
                'confirm_manual_balance' => 'Centang konfirmasi untuk mengubah saldo manual.',
            ]);
        }

        return $data;
    }
}
