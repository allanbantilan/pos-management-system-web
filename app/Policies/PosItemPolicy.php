<?php

namespace App\Policies;

use App\Models\PosItem;

class PosItemPolicy
{
    public function viewAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can view pos item');
    }

    public function view(mixed $authUser, PosItem $posItem): bool
    {
        return $this->can($authUser, 'can view pos item');
    }

    public function create(mixed $authUser): bool
    {
        return $this->can($authUser, 'can create pos item');
    }

    public function update(mixed $authUser, PosItem $posItem): bool
    {
        return $this->can($authUser, 'can update pos item');
    }

    public function delete(mixed $authUser, PosItem $posItem): bool
    {
        return $this->can($authUser, 'can delete pos item');
    }

    public function deleteAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can delete pos item');
    }

    public function forceDelete(mixed $authUser, PosItem $posItem): bool
    {
        return $this->can($authUser, 'can force delete pos item');
    }

    public function forceDeleteAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can force delete pos item');
    }

    public function restore(mixed $authUser, PosItem $posItem): bool
    {
        return $this->can($authUser, 'can restore pos item');
    }

    public function restoreAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can restore pos item');
    }

    public function replicate(mixed $authUser, PosItem $posItem): bool
    {
        return $this->can($authUser, 'can replicate pos item');
    }

    public function reorder(mixed $authUser): bool
    {
        return $this->can($authUser, 'can reorder pos item');
    }

    private function can(mixed $authUser, string $permission): bool
    {
        if (!is_object($authUser) || !method_exists($authUser, 'can')) {
            return false;
        }

        return $authUser->can($permission);
    }
}
