<?php

namespace RobertBoes\SidecarInertiaVite;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Inertia\Ssr\Gateway;
use RobertBoes\SidecarInertiaVite\Cache\CacheStrategy;
use RobertBoes\SidecarInertiaVite\Cache\PageHashStrategy;

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
            $this->app->bind(
                abstract: Gateway::class,
                concrete: SidecarGateway::class,
            );
        }

        if (Config::get('sidecar-inertia-vite.cache.enabled', false)) {
            $this->app->bind(
                abstract: CacheStrategy::class,
                concrete: Config::get('sidecar-inertia-vite.cache.strategy', PageHashStrategy::class),
            );
        }
    }
}
