<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->restaurant_id;
    }

    public function view(User $user, User $model): bool
    {
        return $model->restaurant_id === $user->restaurant_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->restaurant_id;
    }

    public function update(User $user, User $model): bool
    {
        return $model->restaurant_id === $user->restaurant_id;
    }

    public function delete(User $user, User $model): bool
    {
        return $model->restaurant_id === $user->restaurant_id;
    }

    /**
     * Staff management: owner only.
     */
    public function manageStaff(User $user): bool
    {
        return $user->isOwner();
    }
}
