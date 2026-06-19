<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Support\AccessControl;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $selectedRole = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedRole = $data['role'] ?? null;
        unset($data['role']);

        UserResource::validateRoleAndBranch($this->selectedRole, $data['branch_id'] ?? null);

        if ($this->selectedRole === AccessControl::ROLE_OWNER) {
            $data['branch_id'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->selectedRole) {
            $this->record->syncRoles([$this->selectedRole]);
        }
    }
}
