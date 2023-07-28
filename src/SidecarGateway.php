<?php

namespace RobertBoes\SidecarInertiaVite;

use Exception;
use Hammerstone\Sidecar\LambdaFunction;
use Illuminate\Support\Facades\Config;
use Inertia\Ssr\Gateway;
use Inertia\Ssr\Response;
use RobertBoes\SidecarInertiaVite\Cache\CacheStrategy;
use Throwable;

class SidecarGateway implements Gateway
{
    public function __construct(
        protected CacheStrategy $cache
    ) {
        //
    }

    public function dispatch(array $page): ?Response
    {
        if (! Config::get('inertia.ssr.enabled', false)) {
            return null;
        }

        if (! $handler = Config::get('sidecar-inertia-vite.handler')) {
            return null;
        }

        try {
            return $this->execute($handler, $page);
        } catch (Throwable $e) {
            if (Config::get('sidecar-inertia-vite.debug')) {
                throw $e;
            }

            return null;
        }
    }

    protected function execute($handler, array $page): ?Response
    {
        $handler = app($handler);

        if (! $handler instanceof LambdaFunction) {
            throw new Exception('The configured Sidecar SSR Handler is not a Sidecar function.');
        }

        [$head, $body] = $this->cache->execute($page, $handler);

        return new Response(
            head: implode("\n", $head),
            body: $body,
        );
    }
}
