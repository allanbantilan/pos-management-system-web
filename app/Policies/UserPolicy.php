<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can view user');
    }

    public function view(mixed $authUser, User $user): bool
    {
        return $this->can($authUser, 'can view user');
    }

    public function create(mixed $authUser): bool
    {
        return $this->can($authUser, 'can create user');
    }

    public function update(mixed $authUser, User $user): bool
    {
        return $this->can($authUser, 'can update user');
    }

    public function delete(mixed $authUser, User $user): bool
    {
        return $this->can($authUser, 'can delete user');
    }

    public function deleteAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can delete user');
    }

    public function forceDelete(mixed $authUser, User $user): bool
    {
        return $this->can($authUser, 'can force delete user');
    }

    public function forceDeleteAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can force delete user');
    }

    public function restore(mixed $authUser, User $user): bool
    {
        return $this->can($authUser, 'can restore user');
    }

    public function restoreAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can restore user');
    }

    public function replicate(mixed $authUser, User $user): bool
    {
        return $this->can($authUser, 'can replicate user');
    }

    public function reorder(mixed $authUser): bool
    {
        return $this->can($authUser, 'can reorder user');
    }

    private function can(mixed $authUser, string $permission): bool
    {
        if (!is_object($authUser) || !method_exists($authUser, 'can')) {
            return false;
        }

        return $authUser->can($permission);
    }
}
