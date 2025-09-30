<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\CassoService;
use App\Services\ConfigService;
use App\Services\OrganizerService;
use App\Services\EventUserHistoryService;
use App\Services\TransactionService;
use App\Services\NotificationService;
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
        $this->app->singleton(TransactionService::class, fn() => new TransactionService());
        $this->app->singleton(CassoService::class, fn() => new CassoService());
        $this->app->singleton(ConfigService::class, fn() => new ConfigService());
        $this->app->singleton(NotificationService::class, fn() => new NotificationService());
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
