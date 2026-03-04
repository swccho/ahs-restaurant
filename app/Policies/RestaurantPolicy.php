<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;

class RestaurantPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->restaurant_id;
    }

    public function view(User $user, Restaurant $restaurant): bool
    {
        return $restaurant->id === $user->restaurant_id;
    }

    /**
     * Sensitive restaurant settings: owner only.
     */
    public function update(User $user, Restaurant $restaurant): bool
    {
        return $user->isOwner() && $restaurant->id === $user->restaurant_id;
    }
}
