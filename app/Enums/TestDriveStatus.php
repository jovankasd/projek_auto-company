<?php

namespace App\Enums;

enum TestDriveStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Diajukan',
            self::Approved => 'Disetujui',
            self::Completed => 'Selesai',
            self::Rejected => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Requested => 'bg-amber-100 text-amber-700 border-amber-200',
            self::Approved => 'bg-indigo-100 text-indigo-700 border-indigo-200',
            self::Completed => 'bg-green-100 text-green-700 border-green-200',
            self::Rejected => 'bg-red-100 text-red-700 border-red-200',
            self::Cancelled => 'bg-red-100 text-red-700 border-red-200',
        };
    }
}
