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
        if ($service->status->isTerminal()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Mekanik hanya boleh mengubah status work order yang ditugaskan kepadanya,
        // dan tidak boleh langsung mengonfirmasi tiket pending (tugas Super Admin)
        return $user->isMekanik()
            && $service->assigned_mechanic_id === $user->id
            && $service->status !== ServiceStatus::Pending;
    }

    public function addItem(User $user, Service $service): bool
    {
        if ($service->status->isTerminal()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isMekanik() && $service->assigned_mechanic_id === $user->id;
    }

    public function removeItem(User $user, Service $service): bool
    {
        if ($service->status->isTerminal()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isMekanik() && $service->assigned_mechanic_id === $user->id;
    }

    public function assignMechanic(User $user, Service $service): bool
    {
        if ($service->status->isTerminal()) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    public function cancel(User $user, Service $service): bool
    {
        if (! $service->status->canBeCancelled()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isCustomer() && $service->customer_id === $user->id;
    }
}
