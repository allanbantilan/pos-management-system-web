<?php

namespace App\Providers;

use App\Models\AppSetting;
use App\Models\BackendUser;
use App\Models\PosCategory;
use App\Models\PosItem;
use App\Models\Receipt;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\AppSettingPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\BackendUserPolicy;
use App\Policies\PosCategoryPolicy;
use App\Policies\PosItemPolicy;
use App\Policies\ReceiptPolicy;
use App\Policies\RolePolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(BackendUser::class, BackendUserPolicy::class);
        Gate::policy(PosItem::class, PosItemPolicy::class);
        Gate::policy(PosCategory::class, PosCategoryPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Activity::class, AuditLogPolicy::class);
        Gate::policy(AppSetting::class, AppSettingPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Receipt::class, ReceiptPolicy::class);
    }
}
