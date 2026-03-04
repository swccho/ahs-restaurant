<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->restaurant_id;
    }

    public function view(User $user, Category $category): bool
    {
        return $category->restaurant_id === $user->restaurant_id;
    }

    public function create(User $user): bool
    {
        return (bool) $user->restaurant_id;
    }

    public function update(User $user, Category $category): bool
    {
        return $category->restaurant_id === $user->restaurant_id;
    }

    public function delete(User $user, Category $category): bool
    {
        return $category->restaurant_id === $user->restaurant_id;
    }
}
