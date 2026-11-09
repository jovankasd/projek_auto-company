<?php

namespace App\Policies;

use App\Models\Car;
use App\Models\User;

class CarPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Car $car): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isSales() || $user->isSuperAdmin();
    }

    public function update(User $user, Car $car): bool
    {
        return $user->isSales() || $user->isSuperAdmin();
    }

    public function delete(User $user, Car $car): bool
    {
        return $user->isSales() || $user->isSuperAdmin();
    }

    public function restore(User $user, Car $car): bool
    {
        return $user->isSales() || $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Car $car): bool
    {
        return $user->isSuperAdmin();
    }
}
