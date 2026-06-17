<?php

namespace App\Filament\Widgets;

/**
 * POS overview stats without the section heading/description, for the default
 * dashboard. The Statistics page keeps the headed PosOverviewStats (its
 * "selected date range" description matches the filter form on that page).
 */
class DashboardOverviewStats extends PosOverviewStats
{
    protected static bool $isDiscovered = false;

    protected ?string $heading = null;

    protected ?string $description = null;
}
