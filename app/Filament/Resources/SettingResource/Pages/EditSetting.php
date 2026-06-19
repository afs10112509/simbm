<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Pengaturan Aplikasi';
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pengaturan berhasil disimpan';
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Simpan Perubahan');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['logo']) && is_array($data['logo'])) {
            $data['logo'] = $data['logo'][array_key_last($data['logo'])] ?? null;
        }

        return $data;
    }
}