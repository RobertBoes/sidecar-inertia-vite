<?php

namespace RobertBoes\SidecarInertiaVite\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class PageHashStrategy extends CacheStrategy
{
    public function store(array $page, array $result): void
    {
        Cache::put($this->key($page), $result, Config::get('sidecar-inertia-vite.cache.ttl', 30));
    }

    public function get(array $page): ?array
    {
        return Cache::get($this->key($page));
    }

    private function key(array $page): string
    {
        return 'sidecar:inertia-vite:'.hash('sha256', serialize($page));
    }
}
