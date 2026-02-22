<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $defaultScheme = (string) config('branding.default_scheme', 'sunset');
        $schemes = (array) config('branding.schemes', []);
        $defaultPalette = $schemes[$defaultScheme] ?? [
            'primary_color' => '#ea580c',
            'primary_hover_color' => '#f97316',
            'surface_color' => '#fef3c7',
            'background_color' => '#fffbeb',
            'border_color' => '#fde68a',
        ];

        $branding = [
            'pos_name' => 'Fast Food Kiosk',
            'color_scheme' => $defaultScheme,
            'logo_url' => null,
            'primary_color' => (string) $defaultPalette['primary_color'],
            'primary_hover_color' => (string) $defaultPalette['primary_hover_color'],
            'surface_color' => (string) $defaultPalette['surface_color'],
            'background_color' => (string) $defaultPalette['background_color'],
            'border_color' => (string) $defaultPalette['border_color'],
        ];

        if (Schema::hasTable('app_settings')) {
            $settings = AppSetting::current();
            $branding = $settings->resolvedTheme();
        }

        return array_merge(parent::share($request), [
            // Share app name from config
            'appName' => config('app.name', 'Pos System'),
            'branding' => $branding,

            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }
}
