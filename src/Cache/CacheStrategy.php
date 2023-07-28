<?php

namespace RobertBoes\SidecarInertiaVite\Cache;

use Hammerstone\Sidecar\LambdaFunction;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

abstract class CacheStrategy
{
    abstract public function store(array $page, array $result): void;

    abstract public function get(array $page): ?array;

    public function execute(array $page, LambdaFunction $handler): array
    {
        if (! Config::get('sidecar-inertia-vite.cache.enabled', false)) {
            return $this->result($page, $handler);
        }

        if ($result = $this->get($page)) {
            return $result;
        }

        return tap(
            value: $this->result($page, $handler),
            callback: fn (array $result) => $this->store($page, $result),
        );
    }

    protected function result(array $page, LambdaFunction $handler): array
    {
        $result = $handler::execute($page)->throw();

        if (Config::get('sidecar-inertia-vite.timings')) {
            Log::info('Sending SSR request to Lambda', $result->report());
        }

        return $result->body();
    }
}
