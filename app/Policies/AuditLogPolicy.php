<?php

namespace App\Policies;

use Spatie\Activitylog\Models\Activity;

class AuditLogPolicy
{
    public function viewAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can view audit log');
    }

    public function view(mixed $authUser, Activity $activity): bool
    {
        return $this->can($authUser, 'can view audit log');
    }

    public function create(mixed $authUser): bool
    {
        return false;
    }

    public function update(mixed $authUser, Activity $activity): bool
    {
        return false;
    }

    public function delete(mixed $authUser, Activity $activity): bool
    {
        return false;
    }

    public function deleteAny(mixed $authUser): bool
    {
        return false;
    }

    public function forceDelete(mixed $authUser, Activity $activity): bool
    {
        return false;
    }

    public function forceDeleteAny(mixed $authUser): bool
    {
        return false;
    }

    public function restore(mixed $authUser, Activity $activity): bool
    {
        return false;
    }

    public function restoreAny(mixed $authUser): bool
    {
        return false;
    }

    public function replicate(mixed $authUser, Activity $activity): bool
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
