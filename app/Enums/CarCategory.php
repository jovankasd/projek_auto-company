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
            self::Sedan => 'bg-blue-100 text-blue-700 border-blue-200',
            self::Sports => 'bg-red-100 text-red-700 border-red-200',
            self::EV => 'bg-green-100 text-green-700 border-green-200',
            self::MPV => 'bg-purple-100 text-purple-700 border-purple-200',
        };
    }
}
