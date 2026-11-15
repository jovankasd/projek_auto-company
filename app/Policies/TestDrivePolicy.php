<?php

namespace App\Policies;

use App\Models\TestDrive;
use App\Models\User;

class TestDrivePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSales() || $user->isSuperAdmin() || $user->isCustomer();
    }

    public function view(User $user, TestDrive $testDrive): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isSales()) {
            return $testDrive->sales_id === $user->id || is_null($testDrive->sales_id);
        }

        return $testDrive->customer_id === $user->id;
    }

    public function viewLicense(User $user, TestDrive $testDrive): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isSales()) {
            return $testDrive->sales_id === $user->id || is_null($testDrive->sales_id);
        }

        return $testDrive->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isSuperAdmin();
    }

    public function approve(User $user, TestDrive $testDrive): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isSales() && ($testDrive->sales_id === $user->id || is_null($testDrive->sales_id));
    }

    public function reject(User $user, TestDrive $testDrive): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isSales() && ($testDrive->sales_id === $user->id || is_null($testDrive->sales_id));
    }

    public function complete(User $user, TestDrive $testDrive): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isSales() && ($testDrive->sales_id === $user->id || is_null($testDrive->sales_id));
    }

    public function cancel(User $user, TestDrive $testDrive): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $testDrive->customer_id === $user->id;
    }
}
