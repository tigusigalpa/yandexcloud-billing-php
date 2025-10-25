<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Cache;

interface CacheInterface
{
    /**
     * Get cached value by key
     */
    public function get(string $key): ?string;

    /**
     * Store value in cache with TTL
     */
    public function set(string $key, string $value, int $ttl): bool;

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool;

    /**
     * Remove key from cache
     */
    public function delete(string $key): bool;

    /**
     * Clear all cache
     */
    public function clear(): bool;
}
