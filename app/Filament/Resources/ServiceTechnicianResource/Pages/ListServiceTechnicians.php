<?php

namespace App\Filament\Resources\ServiceTechnicianResource\Pages;

use App\Filament\Resources\ServiceTechnicianResource;
use App\Support\AccessControl;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceTechnicians extends ListRecords
{
    protected static string $resource = ServiceTechnicianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Tukang')
                ->visible(fn () => AccessControl::canManageServiceTechnicians()),
        ];
    }
}
