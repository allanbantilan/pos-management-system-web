<?php

namespace App\Policies;

use App\Models\PosCategory;

class PosCategoryPolicy
{
    public function viewAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can view pos category');
    }

    public function view(mixed $authUser, PosCategory $posCategory): bool
    {
        return $this->can($authUser, 'can view pos category');
    }

    public function create(mixed $authUser): bool
    {
        return $this->can($authUser, 'can create pos category');
    }

    public function update(mixed $authUser, PosCategory $posCategory): bool
    {
        return $this->can($authUser, 'can update pos category');
    }

    public function delete(mixed $authUser, PosCategory $posCategory): bool
    {
        return $this->can($authUser, 'can delete pos category');
    }

    public function deleteAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can delete pos category');
    }

    public function forceDelete(mixed $authUser, PosCategory $posCategory): bool
    {
        return $this->can($authUser, 'can force delete pos category');
    }

    public function forceDeleteAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can force delete pos category');
    }

    public function restore(mixed $authUser, PosCategory $posCategory): bool
    {
        return $this->can($authUser, 'can restore pos category');
    }

    public function restoreAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can restore pos category');
    }

    public function replicate(mixed $authUser, PosCategory $posCategory): bool
    {
        return $this->can($authUser, 'can replicate pos category');
    }

    public function reorder(mixed $authUser): bool
    {
        return $this->can($authUser, 'can reorder pos category');
    }

    private function can(mixed $authUser, string $permission): bool
    {
        if (!is_object($authUser) || !method_exists($authUser, 'can')) {
            return false;
        }

        return $authUser->can($permission);
    }
}
