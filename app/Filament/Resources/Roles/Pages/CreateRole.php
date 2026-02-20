<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    /**
     * @var array<int, string>
     */
    protected array $selectedPermissionIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedPermissionIds = $this->extractPermissionIds($data);
        unset($data['permissions_by_model']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncPermissions($this->selectedPermissionIds);
    }

    /**
     * @return array<int, string>
     */
    private function extractPermissionIds(array $data): array
    {
        $ids = collect(data_get($data, 'permissions_by_model', []))
            ->flatten()
            ->filter()
            ->map(static fn ($id): string => (string) $id)
            ->unique()
            ->values()
            ->all();

        return Permission::query()
            ->whereIn('id', $ids)
            ->where('guard_name', $data['guard_name'] ?? 'web')
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->all();
    }
}
