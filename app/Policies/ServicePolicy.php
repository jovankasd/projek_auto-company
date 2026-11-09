<?php

namespace App\Policies;

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isMekanik() || $user->isSuperAdmin() || $user->isCustomer();
    }

    public function view(User $user, Service $service): bool
    {
        if ($user->isSuperAdmin() || $user->isMekanik()) {
            return true;
        }

        return $service->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isSuperAdmin();
    }

    public function updateStatus(User $user, Service $service): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isMekanik()) {
            return $service->assigned_mechanic_id === $user->id;
        }

        return false;
    }

    public function addItem(User $user, Service $service): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isMekanik()) {
            return $service->assigned_mechanic_id === $user->id;
        }

        return false;
    }

    public function removeItem(User $user, Service $service): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isMekanik()) {
            return $service->assigned_mechanic_id === $user->id;
        }

        return false;
    }

    public function assignMechanic(User $user, Service $service): bool
    {
        return $user->isSuperAdmin();
    }

    public function cancel(User $user, Service $service): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $service->customer_id === $user->id && $service->status === ServiceStatus::Pending;
    }
}
