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
            return true;
        }

        return $testDrive->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isSuperAdmin();
    }

    public function approve(User $user, TestDrive $testDrive): bool
    {
        return $user->isSales() || $user->isSuperAdmin();
    }

    public function reject(User $user, TestDrive $testDrive): bool
    {
        return $user->isSales() || $user->isSuperAdmin();
    }

    public function complete(User $user, TestDrive $testDrive): bool
    {
        return $user->isSales() || $user->isSuperAdmin();
    }

    public function cancel(User $user, TestDrive $testDrive): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $testDrive->customer_id === $user->id;
    }

    /**
     * Tentukan apakah pengguna dapat melihat foto SIM test drive (privat & sensitif).
     * Sesuai PRD §3.1-3.3: Hanya super admin, sales yang ditugaskan, atau customer pemilik.
     */
    public function viewDriverLicense(User $user, TestDrive $testDrive): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isSales() && $testDrive->sales_id !== null && $testDrive->sales_id === $user->id) {
            return true;
        }

        return $user->isCustomer() && $testDrive->customer_id === $user->id;
    }

    /**
     * Alias terpadu dari viewDriverLicense untuk konsistensi penamaan route dan action.
     */
    public function viewLicenseImage(User $user, TestDrive $testDrive): bool
    {
        return $this->viewDriverLicense($user, $testDrive);
    }
}
