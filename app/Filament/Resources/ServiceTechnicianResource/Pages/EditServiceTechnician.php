<?php

namespace App\Filament\Resources\ServiceTechnicianResource\Pages;

use App\Filament\Resources\ServiceTechnicianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceTechnician extends EditRecord
{
    protected static string $resource = ServiceTechnicianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
