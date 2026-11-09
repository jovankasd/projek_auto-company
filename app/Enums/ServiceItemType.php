<?php

namespace App\Enums;

enum ServiceItemType: string
{
    case Part = 'part';
    case Labor = 'labor';
    case Consumable = 'consumable';

    public function label(): string
    {
        return match ($this) {
            self::Part => 'Suku Cadang',
            self::Labor => 'Jasa',
            self::Consumable => 'Bahan Habis Pakai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Part => 'bg-blue-100 text-blue-700 border-blue-200',
            self::Labor => 'bg-amber-100 text-amber-700 border-amber-200',
            self::Consumable => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
