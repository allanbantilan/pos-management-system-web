<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AppSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'pos_name',
        'color_scheme',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('app_settings.current'));
        static::deleted(fn () => Cache::forget('app_settings.current'));
    }

    public static function current(): self
    {
        return Cache::rememberForever('app_settings.current', function () {
            return static::query()->firstOrCreate(
                ['id' => 1],
                static::defaults()
            );
        });
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'pos_name' => 'Fast Food Kiosk',
            'color_scheme' => (string) config('branding.default_scheme', 'sunset'),
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('branding-logo')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
            ->singleFile();
    }

    /**
     * @return array<string, string>
     */
    public function resolvedTheme(): array
    {
        $schemeKey = (string) ($this->color_scheme ?: config('branding.default_scheme', 'sunset'));
        $schemes = config('branding.schemes', []);
        $fallbackKey = (string) config('branding.default_scheme', 'sunset');
        $scheme = $schemes[$schemeKey] ?? $schemes[$fallbackKey] ?? [
            'primary_color' => '#ea580c',
            'primary_hover_color' => '#f97316',
            'surface_color' => '#fef3c7',
            'background_color' => '#fffbeb',
            'border_color' => '#fde68a',
        ];

        return [
            'pos_name' => (string) $this->pos_name,
            'color_scheme' => $schemeKey,
            'logo_url' => $this->getFirstMediaUrl('branding-logo') ?: null,
            'primary_color' => (string) $scheme['primary_color'],
            'primary_hover_color' => (string) $scheme['primary_hover_color'],
            'surface_color' => (string) $scheme['surface_color'],
            'background_color' => (string) $scheme['background_color'],
            'border_color' => (string) $scheme['border_color'],
        ];
    }
}
