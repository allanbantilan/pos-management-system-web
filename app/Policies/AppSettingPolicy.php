<?php

namespace App\Policies;

use App\Models\AppSetting;

class AppSettingPolicy
{
    public function viewAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can manage system settings');
    }

    public function view(mixed $authUser, AppSetting $appSetting): bool
    {
        return $this->can($authUser, 'can manage system settings');
    }

    public function create(mixed $authUser): bool
    {
        return false;
    }

    public function update(mixed $authUser, AppSetting $appSetting): bool
    {
        return $this->can($authUser, 'can manage system settings');
    }

    public function delete(mixed $authUser, AppSetting $appSetting): bool
    {
        return false;
    }

    public function deleteAny(mixed $authUser): bool
    {
        return false;
    }

    public function forceDelete(mixed $authUser, AppSetting $appSetting): bool
    {
        return false;
    }

    public function forceDeleteAny(mixed $authUser): bool
    {
        return false;
    }

    public function restore(mixed $authUser, AppSetting $appSetting): bool
    {
        return false;
    }

    public function restoreAny(mixed $authUser): bool
    {
        return false;
    }

    public function replicate(mixed $authUser, AppSetting $appSetting): bool
    {
        return false;
    }

    public function reorder(mixed $authUser): bool
    {
        return false;
    }

    private function can(mixed $authUser, string $permission): bool
    {
        if (!is_object($authUser) || !method_exists($authUser, 'can')) {
            return false;
        }

        return $authUser->can($permission);
    }
}

