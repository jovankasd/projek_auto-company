<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Setting;
use Illuminate\Support\Carbon;

class BookingSlotValidator
{
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

        return max(0, $this->getMaxPerSlot() - $count);
    }

    /**
     * Validasi kuota dan jam operasional slot jam.
     */
    public function isSlotAvailable(string $serviceDate, int $slotHour): bool
    {
        if (! $this->isHourWithinOperatingHours($slotHour)) {
            return false;
        }

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
        return (int) Setting::get('booking.max_per_slot', 3);
    }

    public function getOpenHour(): int
    {
        return (int) Setting::get('booking.open_hour', 8);
    }

    public function getCloseHour(): int
    {
        return (int) Setting::get('booking.close_hour', 17);
    }

    public function isHourWithinOperatingHours(int $hour): bool
    {
        return $hour >= $this->getOpenHour() && $hour <= $this->getCloseHour();
    }
}
