<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Support\Carbon;

class BookingSlotValidator
{
    private const MAX_PER_SLOT = 3;

    /**
     * Sisa kuota slot jam tertentu.
     *
     * Note: Pengecekan ini membaca jumlah record aktif tanpa row/gap lock; lihat ADR-003
     * untuk catatan peningkatan lock transaksi pada skenario konkurensi tinggi.
     */
    public function getRemainingQuota(string $serviceDate, int $slotHour): int
    {
        $date = Carbon::parse($serviceDate)->toDateString();

        $count = Service::whereDate('service_date', $date)
            ->where('slot_hour', $slotHour)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        return max(0, self::MAX_PER_SLOT - $count);
    }

    /**
     * Validasi kuota slot jam (BR-02: maks 3 kendaraan per slot_hour).
     */
    public function isSlotAvailable(string $serviceDate, int $slotHour): bool
    {
        return $this->getRemainingQuota($serviceDate, $slotHour) > 0;
    }

    /**
     * Validasi anti double-booking plat + jam sama (BR-04).
     */
    public function isPlateAvailable(string $serviceDate, int $slotHour, string $plate): bool
    {
        $date = Carbon::parse($serviceDate)->toDateString();

        return ! Service::whereDate('service_date', $date)
            ->where('slot_hour', $slotHour)
            ->where('vehicle_plate_number', strtoupper($plate))
            ->whereNotIn('status', ['cancelled'])
            ->exists();
    }

    public function getMaxPerSlot(): int
    {
        return self::MAX_PER_SLOT;
    }
}
