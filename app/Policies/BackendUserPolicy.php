<?php

namespace App\Policies;

use App\Models\BackendUser;

class BackendUserPolicy
{
    public function viewAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can view backend user');
    }

    public function view(mixed $authUser, BackendUser $backendUser): bool
    {
        return $this->can($authUser, 'can view backend user');
    }

    public function create(mixed $authUser): bool
    {
        return $this->can($authUser, 'can create backend user');
    }

    public function update(mixed $authUser, BackendUser $backendUser): bool
    {
        return $this->can($authUser, 'can update backend user');
    }

    public function delete(mixed $authUser, BackendUser $backendUser): bool
    {
        return $this->can($authUser, 'can delete backend user');
    }

    public function deleteAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can delete backend user');
    }

    public function forceDelete(mixed $authUser, BackendUser $backendUser): bool
    {
        return $this->can($authUser, 'can force delete backend user');
    }

    public function forceDeleteAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can force delete backend user');
    }

    public function restore(mixed $authUser, BackendUser $backendUser): bool
    {
        return $this->can($authUser, 'can restore backend user');
    }

    public function restoreAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can restore backend user');
    }

    public function replicate(mixed $authUser, BackendUser $backendUser): bool
    {
        return $this->can($authUser, 'can replicate backend user');
    }

    public function reorder(mixed $authUser): bool
    {
        return $this->can($authUser, 'can reorder backend user');
    }

    private function can(mixed $authUser, string $permission): bool
    {
        if (!is_object($authUser) || !method_exists($authUser, 'can')) {
            return false;
        }

        return $authUser->can($permission);
    }
}
