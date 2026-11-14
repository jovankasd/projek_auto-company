<?php

namespace App\Support;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Carbon;

class Timezone
{
    public const TIMEZONE = 'Asia/Jakarta';

    public const LABEL = 'WIB';

    /**
     * Konversi objek Carbon/DateTime/string waktu ke zona waktu Asia/Jakarta (WIB).
     */
    public static function toWib(CarbonInterface|DateTimeInterface|string|null $datetime = null): ?Carbon
    {
        if ($datetime === null) {
            return null;
        }

        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return Carbon::instance($datetime)->setTimezone(self::TIMEZONE);
    }

    /**
     * Format timestamp menjadi string presentasi ramah pengguna dalam WIB.
     * Contoh: "14 Nov 2026, 06:30 WIB"
     */
    public static function format(
        CarbonInterface|DateTimeInterface|string|null $datetime,
        string $format = 'd M Y, H:i',
        bool $withSuffix = true
    ): string {
        if ($datetime === null) {
            return '-';
        }

        $wib = self::toWib($datetime);
        if (! $wib) {
            return '-';
        }

        $formatted = $wib->translatedFormat($format);

        return $withSuffix ? "{$formatted} ".self::LABEL : $formatted;
    }

    /**
     * Konversi waktu input lokal (WIB) ke UTC untuk penyimpanan database.
     */
    public static function wibToUtc(string|CarbonInterface|DateTimeInterface $datetime): Carbon
    {
        if (is_string($datetime)) {
            $carbon = Carbon::parse($datetime, self::TIMEZONE);
        } else {
            $carbon = Carbon::instance($datetime)->setTimezone(self::TIMEZONE);
        }

        return $carbon->setTimezone('UTC');
    }

    /**
     * Mendapatkan awal hari (00:00:00) pada tanggal WIB yang dikonversi ke UTC.
     * Misal: 2026-11-14 00:00:00 WIB -> 2026-11-13 17:00:00 UTC.
     */
    public static function startOfDayUtc(string|CarbonInterface|DateTimeInterface $date): Carbon
    {
        if (is_string($date)) {
            $carbon = Carbon::parse($date, self::TIMEZONE);
        } else {
            $carbon = Carbon::instance($date)->setTimezone(self::TIMEZONE);
        }

        return $carbon->startOfDay()->setTimezone('UTC');
    }

    /**
     * Mendapatkan akhir hari (23:59:59.999999) pada tanggal WIB yang dikonversi ke UTC.
     * Misal: 2026-11-14 23:59:59 WIB -> 2026-11-14 16:59:59 UTC.
     */
    public static function endOfDayUtc(string|CarbonInterface|DateTimeInterface $date): Carbon
    {
        if (is_string($date)) {
            $carbon = Carbon::parse($date, self::TIMEZONE);
        } else {
            $carbon = Carbon::instance($date)->setTimezone(self::TIMEZONE);
        }

        return $carbon->endOfDay()->setTimezone('UTC');
    }

    /**
     * Waktu saat ini dalam Asia/Jakarta (WIB).
     */
    public static function nowWib(): Carbon
    {
        return Carbon::now(self::TIMEZONE);
    }

    /**
     * Hari ini dalam Asia/Jakarta (WIB).
     */
    public static function todayWib(): Carbon
    {
        return Carbon::today(self::TIMEZONE);
    }
}
