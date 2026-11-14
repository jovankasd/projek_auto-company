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

    /**
     * Transisi status yang valid dari status ini sesuai PRD §5.2.
     *
     * @return array<TestDriveStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Requested => [self::Approved, self::Rejected, self::Cancelled],
            self::Approved => [self::Completed, self::Cancelled],
            self::Completed => [],
            self::Rejected => [],
            self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed, self::Rejected, self::Cancelled => true,
            default => false,
        };
    }

    public function canBeCancelled(): bool
    {
        return match ($this) {
            self::Requested, self::Approved => true,
            default => false,
        };
    }
}
