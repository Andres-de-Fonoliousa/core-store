<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Observers\TenantObserver;
use App\Services\Tenant\PlanFeatures;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\TenantResolver;
use App\Services\Tenant\TenantScope;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Pulse\Pulse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantManager::class);
        $this->app->singleton(TenantScope::class, function () {
            return new TenantScope();
        });
        $this->app->singleton(TenantResolver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configurePulse();
        Tenant::observe(TenantObserver::class);
    }

    protected function configurePulse(): void
    {
        Gate::define('viewPulse', function (?\Illuminate\Contracts\Auth\Authenticatable $user) {
            return $user && $user->role === 'admin';
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()),
        );

        RateLimiter::for('tenant-api', function (Request $request) {
            $tenant = app(TenantManager::class)->getCurrent();
            $maxPerMinute = $tenant ? PlanFeatures::apiRateLimit($tenant) : 60;

            return Limit::perMinute($maxPerMinute)
                ->by('tenant:' . ($tenant?->id ?? 'global') . '|user:' . ($request->user()?->id ?? 'guest'));
        });
    }
}
