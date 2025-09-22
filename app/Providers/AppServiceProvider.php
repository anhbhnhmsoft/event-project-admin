<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\OrganizerService;
use App\Services\EventUserHistoryService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthService::class, fn() => new AuthService());
        $this->app->singleton(OrganizerService::class, fn() => new OrganizerService());
        $this->app->singleton(EventUserHistoryService::class, fn() => new EventUserHistoryService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->is('admin*')) {
            App::setLocale('vi');
        }
    }
}
