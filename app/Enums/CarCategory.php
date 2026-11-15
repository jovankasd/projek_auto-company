<?php

namespace App\Enums;

enum CarCategory: string
{
    case SUV = 'SUV';
    case Sedan = 'Sedan';
    case Sports = 'Sports';
    case EV = 'EV';
    case MPV = 'MPV';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::SUV => 'bg-slate-100 text-slate-700 border-slate-200',
            self::Sedan => 'bg-blue-50 text-blue-700 border-blue-200',
            self::Sports => 'bg-red-50 text-red-700 border-red-200',
            self::EV => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::MPV => 'bg-amber-50 text-amber-800 border-amber-200',
        };
    }
}
