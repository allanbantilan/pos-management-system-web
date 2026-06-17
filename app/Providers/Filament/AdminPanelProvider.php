<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Statistics;
use App\Filament\Widgets\PosOverviewStats;
use App\Filament\Widgets\SalesProfitTrendChart;
use App\Filament\Widgets\TopSellingItemsTable;
use App\Filament\Widgets\UserWelcomeWidget;
use Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('POS Command Center')
            ->authGuard('backend')
            ->login()
            ->font('Space Grotesk', 'https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700')
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop()
            ->darkMode()
            ->colors([
                'primary' => Color::hex('#d45a1f'),
                'success' => Color::hex('#16865b'),
                'warning' => Color::hex('#b7791f'),
                'danger' => Color::hex('#d14b4b'),
            ])
            ->navigationGroups([
                NavigationGroup::make('POS Management'),
                NavigationGroup::make('Statistics'),
                NavigationGroup::make('Access Control'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                Statistics::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                UserWelcomeWidget::class,
                PosOverviewStats::class,
                SalesProfitTrendChart::class,
                TopSellingItemsTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
