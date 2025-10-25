<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Cache;

use Redis;
use RedisException;

class RedisCache implements CacheInterface
{
    private Redis $redis;
    private string $prefix;

    public function __construct(Redis $redis = null, string $prefix = 'yandex_cloud:')
    {
        $this->redis = $redis ?? new Redis();
        $this->prefix = $prefix;

        // Подключаемся к Redis если еще не подключены
        if (!$this->redis->isConnected()) {
            $this->redis->connect('127.0.0.1', 6379);
        }
    }

    public function get(string $key): ?string
    {
        try {
            $value = $this->redis->get($this->getKey($key));
            return $value === false ? null : $value;
        } catch (RedisException $e) {
            return null;
        }
    }

    public function set(string $key, string $value, int $ttl): bool
    {
        try {
            return $this->redis->setex($this->getKey($key), $ttl, $value);
        } catch (RedisException $e) {
            return false;
        }
    }

    public function has(string $key): bool
    {
        try {
            return $this->redis->exists($this->getKey($key)) > 0;
        } catch (RedisException $e) {
            return false;
        }
    }

    public function delete(string $key): bool
    {
        try {
            return $this->redis->del($this->getKey($key)) > 0;
        } catch (RedisException $e) {
            return false;
        }
    }

    public function clear(): bool
    {
        try {
            $keys = $this->redis->keys($this->prefix . '*');
            if (empty($keys)) {
                return true;
            }
            return $this->redis->del($keys) > 0;
        } catch (RedisException $e) {
            return false;
        }
    }

    /**
     * Get prefixed key
     */
    private function getKey(string $key): string
    {
        return $this->prefix . $key;
    }

    /**
     * Get Redis instance
     */
    public function getRedis(): Redis
    {
        return $this->redis;
    }
}
