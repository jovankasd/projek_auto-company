<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    /**
     * Nilai default aman bila database belum memiliki record untuk key tertentu.
     */
    public const DEFAULTS = [
        'booking.open_hour' => 8,
        'booking.close_hour' => 17,
        'booking.max_per_slot' => 3,
        'contact.whatsapp' => '6281234567890',
        'contact.email' => 'info@xyzmotors.id',
        'contact.address' => 'Jl. Sudirman No. 88, Jakarta Selatan 12190',
        'seo.site_name' => 'Dealer Pro',
        'seo.tagline' => 'Drive Your Dream',
    ];

    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            Cache::forget("setting.{$setting->key}");
            Cache::forget('settings.all');
        });

        static::deleted(function (Setting $setting) {
            Cache::forget("setting.{$setting->key}");
            Cache::forget('settings.all');
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $resolvedDefault = $default ?? self::DEFAULTS[$key] ?? null;

        return Cache::rememberForever("setting.{$key}", function () use ($key, $resolvedDefault) {
            $setting = static::where('key', $key)->first();

            if (! $setting || $setting->value === null) {
                return $resolvedDefault;
            }

            return match ($setting->type) {
                'integer' => (int) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): self
    {
        $stringValue = match ($type) {
            'json' => is_array($value) ? json_encode($value) : (string) $value,
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'group' => $group,
                'type' => $type,
            ]
        );

        Cache::forget("setting.{$key}");
        Cache::forget('settings.all');

        return $setting;
    }
}
