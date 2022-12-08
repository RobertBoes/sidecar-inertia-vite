<?php

namespace RobertBoes\SidecarInertiaVite;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Inertia\Ssr\Gateway;

class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('sidecar-inertia-vite.php'),
            ], 'config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'sidecar-inertia-vite');

        if (Config::get('sidecar-inertia-vite.ssr_gateway_enabled', false)) {
            $this->app->instance(Gateway::class, new SidecarGateway());
        }
    }
}
