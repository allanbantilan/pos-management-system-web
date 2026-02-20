<?php

namespace App\Policies;

use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can view role');
    }

    public function view(mixed $authUser, Role $role): bool
    {
        return $this->can($authUser, 'can view role');
    }

    public function create(mixed $authUser): bool
    {
        return $this->can($authUser, 'can create role');
    }

    public function update(mixed $authUser, Role $role): bool
    {
        return $this->can($authUser, 'can update role');
    }

    public function delete(mixed $authUser, Role $role): bool
    {
        return $this->can($authUser, 'can delete role');
    }

    public function deleteAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can delete role');
    }

    public function forceDelete(mixed $authUser, Role $role): bool
    {
        return $this->can($authUser, 'can force delete role');
    }

    public function forceDeleteAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can force delete role');
    }

    public function restore(mixed $authUser, Role $role): bool
    {
        return $this->can($authUser, 'can restore role');
    }

    public function restoreAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can restore role');
    }

    public function replicate(mixed $authUser, Role $role): bool
    {
        return $this->can($authUser, 'can replicate role');
    }

    public function reorder(mixed $authUser): bool
    {
        return $this->can($authUser, 'can reorder role');
    }

    private function can(mixed $authUser, string $permission): bool
    {
        if (!is_object($authUser) || !method_exists($authUser, 'can')) {
            return false;
        }

        return $authUser->can($permission);
    }
}
