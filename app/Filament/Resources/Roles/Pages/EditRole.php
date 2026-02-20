<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Roles\Schemas\RoleForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    /**
     * @var array<int, string>
     */
    protected array $selectedPermissionIds = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Role $role */
        $role = $this->record;

        $grouped = [
            'user' => [],
            'backend_user' => [],
            'pos_item' => [],
            'pos_category' => [],
            'role' => [],
            'general' => [],
        ];

        foreach ($role->permissions()->get(['id', 'name']) as $permission) {
            $key = RoleForm::permissionGroupKey($permission->name);
            $grouped[$key][] = (string) $permission->id;
        }

        $data['permissions_by_model'] = $grouped;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedPermissionIds = $this->extractPermissionIds($data);
        unset($data['permissions_by_model']);

        return $data;
    }

    protected function afterSave(): void
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
