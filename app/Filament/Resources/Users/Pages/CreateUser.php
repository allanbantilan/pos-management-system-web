<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Client users are employees: auto-assign the default employee role so they
     * are never role-less (cashier carries the POS abilities, e.g. process sale).
     */
    protected function afterCreate(): void
    {
        $this->record->assignRole('cashier');
    }
}
