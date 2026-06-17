<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class UserWelcomeWidget extends Widget
{
    protected string $view = 'filament.widgets.user-welcome-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -10;

    // Registered explicitly in the panel; skip auto-discovery to avoid duplicates.
    protected static bool $isDiscovered = false;

    public function welcomeName(): string
    {
        return (string) (Filament::auth()->user()?->name ?? 'there');
    }

    public function welcomeRole(): string
    {
        $user = Filament::auth()->user();

        $roles = ($user && method_exists($user, 'getRoleNames'))
            ? $user->getRoleNames()->implode(', ')
            : '';

        return $roles !== '' ? $roles : 'No role assigned';
    }
}
