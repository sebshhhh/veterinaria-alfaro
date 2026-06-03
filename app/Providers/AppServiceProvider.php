<?php

namespace App\Providers;

use App\Models\Citas;
use App\Models\ConfiguracionSistema;
use App\Services\WorkspaceNotificationService;
use App\Support\WindowsSafeFilesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('files', fn () => new WindowsSafeFilesystem());

        $this->app->singleton(WorkspaceNotificationService::class, function () {
            return new WorkspaceNotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            $host = request()->getHost();

            if (str_ends_with($host, 'trycloudflare.com')) {
                URL::forceRootUrl('https://' . $host);
                URL::forceScheme('https');
            }
        }

        View::composer('layouts.navigation', function ($view) {
            $today = now()->toDateString();
            $currentTime = now()->format('H:i');

            $proximaCitaSidebar = Citas::with(['mascota.cliente'])
                ->where('estado', 'pendiente')
                ->where(function ($query) use ($today, $currentTime) {
                    $query->whereDate('fecha', '>', $today)
                        ->orWhere(function ($subQuery) use ($today, $currentTime) {
                            $subQuery->whereDate('fecha', $today)
                                ->where('hora', '>=', $currentTime);
                        });
                })
                ->orderBy('fecha')
                ->orderBy('hora')
                ->first();

            $view->with('proximaCitaSidebar', $proximaCitaSidebar);
            $view->with('workspaceClinicSettings', ConfiguracionSistema::valores());
        });

        View::composer('layouts.app', function ($view) {
            $view->with('workspaceNotifications', app(WorkspaceNotificationService::class)->build());
        });
    }
}
