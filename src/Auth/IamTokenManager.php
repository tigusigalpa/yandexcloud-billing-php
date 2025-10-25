<?php

namespace Tigusigalpa\YandexCloudBilling\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Tigusigalpa\YandexCloudBilling\Cache\CacheInterface;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;

class IamTokenManager
{
    private Client $httpClient;
    private string $oauthToken;
    private ?string $iamToken = null;
    private ?int $iamTokenExpiry = null;
    private ?CacheInterface $cache = null;

    const IAM_TOKEN_ENDPOINT = 'https://iam.api.cloud.yandex.net/iam/v1/tokens';

    public function __construct(string $oauthToken, ?Client $httpClient = null, ?CacheInterface $cache = null)
    {
        if (empty($oauthToken)) {
            throw new AuthenticationException('OAuth token cannot be empty');
        }

        $this->oauthToken = $oauthToken;
        $this->httpClient = $httpClient ?? new Client();
        $this->cache = $cache;
    }

    /**
     * Get valid IAM token (with caching and auto-refresh)
     *
     * @return string
     * @throws AuthenticationException
     */
    public function getValidIamToken(): string
    {
        // Сначала пытаемся получить токен из кэша
        if ($this->cache !== null) {
            $cacheKey = $this->getCacheKey();
            $cachedToken = $this->cache->get($cacheKey);
            
            if ($cachedToken !== null) {
                $this->iamToken = $cachedToken;
                // Устанавливаем время истечения с запасом
                $this->iamTokenExpiry = time() + (12 * 60 * 60) - 300;
                return $this->iamToken;
            }
        }

        // Проверяем, нужно ли обновить токен (токены действуют 12 часов)
        if ($this->iamToken === null || $this->iamTokenExpiry === null || time() >= $this->iamTokenExpiry) {
            $this->refreshIamToken();
        }

        return $this->iamToken;
    }

    /**
     * Get IAM token using OAuth token
     *
     * @return string
     * @throws AuthenticationException
     */
    public function getIamToken(): string
    {
        try {
            $response = $this->httpClient->post(self::IAM_TOKEN_ENDPOINT, [
                'json' => [
                    'yandexPassportOauthToken' => $this->oauthToken,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            if ($statusCode !== 200) {
                $errorData = json_decode($responseBody, true);
                $errorMessage = $errorData['message'] ?? 'Unknown error';
                throw new AuthenticationException("Failed to get IAM token (HTTP {$statusCode}): {$errorMessage}");
            }

            $data = json_decode($responseBody, true);

            if (!isset($data['iamToken'])) {
                throw new AuthenticationException('IAM token not found in response');
            }

            return $data['iamToken'];
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Error getting IAM token: ' . $e->getMessage());
        }
    }

    /**
     * Refresh IAM token and update cache
     *
     * @throws AuthenticationException
     */
    private function refreshIamToken(): void
    {
        $this->iamToken = $this->getIamToken();
        // IAM токены действуют 12 часов, устанавливаем время истечения с запасом в 5 минут
        $this->iamTokenExpiry = time() + (12 * 60 * 60) - 300;
        
        // Сохраняем в кэш
        if ($this->cache !== null && $this->iamToken !== null) {
            $cacheKey = $this->getCacheKey();
            $ttl = (12 * 60 * 60) - 300; // 12 часов минус 5 минут
            $this->cache->set($cacheKey, $this->iamToken, $ttl);
        }
    }

    /**
     * Clear cached IAM token (force refresh on next request)
     */
    public function clearCache(): void
    {
        $this->iamToken = null;
        $this->iamTokenExpiry = null;
        
        // Очищаем кэш
        if ($this->cache !== null) {
            $cacheKey = $this->getCacheKey();
            $this->cache->delete($cacheKey);
        }
    }

    /**
     * Check if cached IAM token is still valid
     *
     * @return bool
     */
    public function hasValidCachedToken(): bool
    {
        return $this->iamToken !== null 
            && $this->iamTokenExpiry !== null 
            && time() < $this->iamTokenExpiry;
    }

    /**
     * Get OAuth token (for debugging purposes)
     *
     * @return string
     */
    public function getOAuthToken(): string
    {
        return $this->oauthToken;
    }

    /**
     * Get cache key for IAM token
     *
     * @return string
     */
    private function getCacheKey(): string
    {
        return 'iam_token_' . md5($this->oauthToken);
    }
}