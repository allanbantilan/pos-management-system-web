<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Models\BackendUser;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
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

        $query = Permission::query()
            ->whereIn('id', $ids)
            ->where('guard_name', $data['guard_name'] ?? 'web');

        // Privilege-escalation guard: a non super-admin may only grant
        // permissions they already hold themselves.
        $actor = Auth::guard('backend')->user();
        if (! $actor instanceof BackendUser || ! $actor->hasRole('backend-admin')) {
            $allowedNames = $actor?->getAllPermissions()->pluck('name')->all() ?? [];
            $query->whereIn('name', $allowedNames);
        }

        return $query
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->all();
    }
}
