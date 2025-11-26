<?php

namespace App\Providers;

use App\Services\TenantService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantService::class, function ($app) {
            return new TenantService();
        });
        $companyId = config('app.company_id', 1); // default to 1
        $this->app->make(TenantService::class)->setCompanyId($companyId);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(1000);
        });
    }
}
