<?php

namespace App\Enums;

enum SparePartCategory: string
{
    case Engine = 'Engine';
    case Brake = 'Brake';
    case Suspension = 'Suspension';
    case Electrical = 'Electrical';
    case Body = 'Body';
    case Interior = 'Interior';
    case Transmission = 'Transmission';
    case Exhaust = 'Exhaust';
    case Cooling = 'Cooling';
    case Accessories = 'Accessories';

    public function label(): string
    {
        return match ($this) {
            self::Engine => 'Mesin',
            self::Brake => 'Rem',
            self::Suspension => 'Suspensi',
            self::Electrical => 'Kelistrikan',
            self::Body => 'Bodi',
            self::Interior => 'Interior',
            self::Transmission => 'Transmisi',
            self::Exhaust => 'Knalpot',
            self::Cooling => 'Pendingin',
            self::Accessories => 'Aksesoris',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Engine => 'bg-red-100 text-red-700 border-red-200',
            self::Brake => 'bg-orange-100 text-orange-700 border-orange-200',
            self::Suspension => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            self::Electrical => 'bg-blue-100 text-blue-700 border-blue-200',
            self::Body => 'bg-slate-100 text-slate-700 border-slate-200',
            self::Interior => 'bg-stone-100 text-stone-700 border-stone-200',
            self::Transmission => 'bg-purple-100 text-purple-700 border-purple-200',
            self::Exhaust => 'bg-zinc-100 text-zinc-700 border-zinc-200',
            self::Cooling => 'bg-cyan-100 text-cyan-700 border-cyan-200',
            self::Accessories => 'bg-pink-100 text-pink-700 border-pink-200',
        };
    }
}
