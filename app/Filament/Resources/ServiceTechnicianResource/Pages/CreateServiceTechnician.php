<?php

namespace App\Filament\Resources\ServiceTechnicianResource\Pages;

use App\Filament\Resources\ServiceTechnicianResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceTechnician extends CreateRecord
{
    protected static string $resource = ServiceTechnicianResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Tukang service berhasil ditambahkan';
    }
}
