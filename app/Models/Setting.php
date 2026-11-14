<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

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
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (! $setting || $setting->value === null) {
                return $default;
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
            'json' => is_array($value) ? json_encode($value) : ($value === null ? null : (string) $value),
            'boolean' => $value ? '1' : '0',
            default => $value === null ? null : (string) $value,
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
