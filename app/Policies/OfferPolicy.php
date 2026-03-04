<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;

class OfferPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->restaurant_id;
    }

    public function view(User $user, Offer $offer): bool
    {
        return $offer->restaurant_id === $user->restaurant_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->restaurant_id;
    }

    public function update(User $user, Offer $offer): bool
    {
        return $offer->restaurant_id === $user->restaurant_id;
    }

    public function delete(User $user, Offer $offer): bool
    {
        return $offer->restaurant_id === $user->restaurant_id;
    }
}
