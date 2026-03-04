<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\User;

class MenuItemPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->restaurant_id;
    }

    public function view(User $user, MenuItem $menuItem): bool
    {
        return $menuItem->restaurant_id === $user->restaurant_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->restaurant_id;
    }

    public function update(User $user, MenuItem $menuItem): bool
    {
        return $menuItem->restaurant_id === $user->restaurant_id;
    }

    public function delete(User $user, MenuItem $menuItem): bool
    {
        return $menuItem->restaurant_id === $user->restaurant_id;
    }
}
