<?php

namespace App\Providers;

use App\Models\BackendUser;
use App\Models\PosCategory;
use App\Models\PosItem;
use App\Models\User;
use App\Policies\BackendUserPolicy;
use App\Policies\PosCategoryPolicy;
use App\Policies\PosItemPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(BackendUser::class, BackendUserPolicy::class);
        Gate::policy(PosItem::class, PosItemPolicy::class);
        Gate::policy(PosCategory::class, PosCategoryPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
    }
}
