<?php

namespace App\Policies;

use App\Models\Transaction;

class TransactionPolicy
{
    public function viewAny(mixed $authUser): bool
    {
        return $this->can($authUser, 'can view dashboard analytics');
    }

    public function view(mixed $authUser, Transaction $transaction): bool
    {
        return $this->can($authUser, 'can view dashboard analytics');
    }

    public function create(mixed $authUser): bool
    {
        return false;
    }

    public function update(mixed $authUser, Transaction $transaction): bool
    {
        return false;
    }

    public function delete(mixed $authUser, Transaction $transaction): bool
    {
        return false;
    }

    public function deleteAny(mixed $authUser): bool
    {
        return false;
    }

    public function forceDelete(mixed $authUser, Transaction $transaction): bool
    {
        return false;
    }

    public function forceDeleteAny(mixed $authUser): bool
    {
        return false;
    }

    public function restore(mixed $authUser, Transaction $transaction): bool
    {
        return false;
    }

    public function restoreAny(mixed $authUser): bool
    {
        return false;
    }

    public function replicate(mixed $authUser, Transaction $transaction): bool
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

