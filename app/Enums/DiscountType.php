<?php

namespace App\Enums;

enum DiscountType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';
    case Bonus = 'bonus';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Persentase',
            self::Fixed => 'Potongan Tetap',
            self::Bonus => 'Bonus',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Percentage => 'bg-blue-50 text-blue-700 border-blue-200',
            self::Fixed => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Bonus => 'bg-amber-50 text-amber-800 border-amber-200',
        };
    }
}
