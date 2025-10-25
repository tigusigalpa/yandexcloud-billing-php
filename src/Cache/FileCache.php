<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Cache;

class FileCache implements CacheInterface
{
    private string $cacheDir;
    private string $prefix;

    public function __construct(string $cacheDir = null, string $prefix = 'yandex_cloud_')
    {
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'yandex_cloud_cache';
        $this->prefix = $prefix;

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get(string $key): ?string
    {
        $filePath = $this->getFilePath($key);
        
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (!$data || !isset($data['expires_at'], $data['value'])) {
            $this->delete($key);
            return null;
        }

        // Проверяем срок действия
        if ($data['expires_at'] < time()) {
            $this->delete($key);
            return null;
        }

        return $data['value'];
    }

    public function set(string $key, string $value, int $ttl): bool
    {
        $filePath = $this->getFilePath($key);
        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl,
            'created_at' => time(),
        ];

        $result = file_put_contents($filePath, json_encode($data), LOCK_EX);
        return $result !== false;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function delete(string $key): bool
    {
        $filePath = $this->getFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true;
    }

    public function clear(): bool
    {
        if (!is_dir($this->cacheDir)) {
            return true;
        }

        $files = glob($this->cacheDir . DIRECTORY_SEPARATOR . $this->prefix . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Get file path for cache key
     */
    private function getFilePath(string $key): string
    {
        $hashedKey = md5($this->prefix . $key);
        return $this->cacheDir . DIRECTORY_SEPARATOR . $this->prefix . $hashedKey . '.cache';
    }

    /**
     * Clean expired cache files
     */
    public function cleanExpired(): int
    {
        $cleaned = 0;
        $files = glob($this->cacheDir . DIRECTORY_SEPARATOR . $this->prefix . '*.cache');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            $data = json_decode($content, true);
            if (!$data || !isset($data['expires_at'])) {
                unlink($file);
                $cleaned++;
                continue;
            }

            if ($data['expires_at'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }
}
