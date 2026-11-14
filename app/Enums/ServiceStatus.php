<?php

namespace App\Enums;

enum ServiceStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Confirmed => 'Dikonfirmasi',
            self::InProgress => 'Dikerjakan',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-700 border-amber-200',
            self::Confirmed => 'bg-indigo-100 text-indigo-700 border-indigo-200',
            self::InProgress => 'bg-blue-100 text-blue-700 border-blue-200',
            self::Completed => 'bg-green-100 text-green-700 border-green-200',
            self::Cancelled => 'bg-red-100 text-red-700 border-red-200',
        };
    }

    /**
     * Transisi yang valid dari status ini.
     *
     * @return array<ServiceStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::InProgress, self::Cancelled],
            self::InProgress => [self::Completed],
            self::Completed => [],
            self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return $this === self::Completed || $this === self::Cancelled;
    }
}
