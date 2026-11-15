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
            self::Customer => 'bg-blue-50 text-blue-700 border-blue-200',
            self::Sales => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Mekanik => 'bg-amber-50 text-amber-800 border-amber-200',
            self::SuperAdmin => 'bg-slate-900 text-white border-slate-900',
            self::Owner => 'bg-slate-100 text-slate-900 border-slate-300',
        };
    }
}
