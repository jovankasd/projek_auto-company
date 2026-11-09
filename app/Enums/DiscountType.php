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
            self::Percentage => 'bg-blue-100 text-blue-700 border-blue-200',
            self::Fixed => 'bg-green-100 text-green-700 border-green-200',
            self::Bonus => 'bg-purple-100 text-purple-700 border-purple-200',
        };
    }
}
