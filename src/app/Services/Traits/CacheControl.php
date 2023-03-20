<?php

namespace ArchCrudLaravel\App\Services\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheControl
{
    protected bool $onCache = true;
    protected $nameModel;
    protected $request;

    protected function putCache(string $key, mixed $value, int $ttl = 3600)
    {
        if ($this->onCache) {
            Cache::put(
                key: $key,
                value: $value,
                ttl: $ttl
            );
        }
    }

    protected function getCache(string $key): mixed
    {
        if ($this->onCache) {
            return Cache::get(
                key: $key,
                default: null
            );
        }
        return null;
    }

    protected function createCacheKey(int|string|null $id = null): string
    {
        return md5($this->nameModel . $id . json_encode($this->request));
    }
}