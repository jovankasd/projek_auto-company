<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Sales = 'sales';
    case Mekanik = 'mekanik';
    case SuperAdmin = 'super_admin';
    case Owner = 'owner';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Sales => 'Sales',
            self::Mekanik => 'Admin Mekanik',
            self::SuperAdmin => 'Super Admin',
            self::Owner => 'Owner',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Customer => 'bg-blue-100 text-blue-700 border-blue-200',
            self::Sales => 'bg-green-100 text-green-700 border-green-200',
            self::Mekanik => 'bg-amber-100 text-amber-700 border-amber-200',
            self::SuperAdmin => 'bg-slate-900 text-white border-slate-700',
            self::Owner => 'bg-purple-100 text-purple-800 border-purple-200',
        };
    }
}
