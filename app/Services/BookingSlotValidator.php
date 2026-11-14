<?php

namespace App\Services;

use App\Enums\ServiceStatus;
use App\Models\Service;
use Illuminate\Support\Carbon;

class BookingSlotValidator
{
    public const MAX_PER_SLOT = 3;

    /**
     * Normalisasi nomor plat menjadi bentuk kanonikal (huruf besar tanpa spasi).
     * Contoh: "b  1234  xyz" -> "B1234XYZ", "B 1234 XYZ" -> "B1234XYZ".
     */
    public static function normalizePlate(string $plate): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($plate)));
    }

    /**
     * Menghasilkan string representasi kunci unik slot jam (YYYY-MM-DD:HH).
     */
    public function getSlotKey(string $serviceDate, int $slotHour): string
    {
        $date = Carbon::parse($serviceDate)->toDateString();

        return sprintf('%s:%02d', $date, $slotHour);
    }

    /**
     * Sisa kuota slot jam tertentu.
     */
    public function getRemainingQuota(string $serviceDate, int $slotHour): int
    {
        $date = Carbon::parse($serviceDate)->toDateString();
        $slotKey = $this->getSlotKey($serviceDate, $slotHour);

        $count = Service::where(function ($query) use ($date, $slotHour, $slotKey) {
            $query->where('slot_key', $slotKey)
                ->orWhere(function ($subQuery) use ($date, $slotHour) {
                    $subQuery->whereDate('service_date', $date)->where('slot_hour', $slotHour);
                });
        })
            ->whereNotIn('status', ['cancelled', ServiceStatus::Cancelled->value])
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
        $slotKey = $this->getSlotKey($serviceDate, $slotHour);
        $normalizedPlate = self::normalizePlate($plate);
        $activePlateSlot = sprintf('%s:%s', $slotKey, $normalizedPlate);

        return ! Service::where(function ($query) use ($date, $slotHour, $slotKey, $normalizedPlate, $activePlateSlot) {
            $query->where('active_plate_slot', $activePlateSlot)
                ->orWhere(function ($subQuery) use ($date, $slotHour, $slotKey, $normalizedPlate) {
                    $subQuery->where(function ($q) use ($date, $slotHour, $slotKey) {
                        $q->where('slot_key', $slotKey)
                            ->orWhere(function ($inner) use ($date, $slotHour) {
                                $inner->whereDate('service_date', $date)->where('slot_hour', $slotHour);
                            });
                    })
                        ->where(function ($pQuery) use ($normalizedPlate) {
                            $pQuery->where('vehicle_plate_number', $normalizedPlate)
                                ->orWhereRaw("UPPER(REPLACE(vehicle_plate_number, ' ', '')) = ?", [$normalizedPlate]);
                        });
                });
        })
            ->whereNotIn('status', ['cancelled', ServiceStatus::Cancelled->value])
            ->exists();
    }

    public function getMaxPerSlot(): int
    {
        return self::MAX_PER_SLOT;
    }
}
