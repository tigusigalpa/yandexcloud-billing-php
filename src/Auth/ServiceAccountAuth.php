<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Tigusigalpa\YandexCloudBilling\Cache\CacheInterface;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;

class ServiceAccountAuth
{
    private Client $httpClient;
    private string $serviceAccountId;
    private string $keyId;
    private string $privateKey;
    private ?string $iamToken = null;
    private ?int $iamTokenExpiry = null;
    private ?CacheInterface $cache = null;

    const IAM_TOKEN_ENDPOINT = 'https://iam.api.cloud.yandex.net/iam/v1/tokens';

    public function __construct(
        string $serviceAccountId,
        string $keyId,
        string $privateKey,
        ?Client $httpClient = null,
        ?CacheInterface $cache = null
    ) {
        if (empty($serviceAccountId)) {
            throw new AuthenticationException('Service Account ID cannot be empty');
        }

        if (empty($keyId)) {
            throw new AuthenticationException('Key ID cannot be empty');
        }

        if (empty($privateKey)) {
            throw new AuthenticationException('Private key cannot be empty');
        }

        $this->serviceAccountId = $serviceAccountId;
        $this->keyId = $keyId;
        $this->privateKey = $privateKey;
        $this->httpClient = $httpClient ?? new Client();
        $this->cache = $cache;
    }

    /**
     * Get valid IAM token using Service Account JWT
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

        if ($this->iamToken === null || $this->iamTokenExpiry === null || time() >= $this->iamTokenExpiry) {
            $this->refreshIamToken();
        }

        return $this->iamToken;
    }

    /**
     * Create JWT token for Service Account
     *
     * @return string
     * @throws AuthenticationException
     */
    private function createJwtToken(): string
    {
        $now = time();
        $payload = [
            'aud' => self::IAM_TOKEN_ENDPOINT,
            'iss' => $this->serviceAccountId,
            'iat' => $now,
            'exp' => $now + 3600, // JWT токен действует 1 час
        ];

        try {
            return JWT::encode($payload, $this->privateKey, 'PS256', $this->keyId);
        } catch (\Exception $e) {
            throw new AuthenticationException('Failed to create JWT token: ' . $e->getMessage());
        }
    }

    /**
     * Exchange JWT token for IAM token
     *
     * @return string
     * @throws AuthenticationException
     */
    private function getIamTokenFromJwt(): string
    {
        try {
            $jwtToken = $this->createJwtToken();

            $response = $this->httpClient->post(self::IAM_TOKEN_ENDPOINT, [
                'json' => [
                    'jwt' => $jwtToken,
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
            throw new AuthenticationException('Error getting IAM token from JWT: ' . $e->getMessage());
        }
    }

    /**
     * Refresh IAM token and update cache
     *
     * @throws AuthenticationException
     */
    private function refreshIamToken(): void
    {
        $this->iamToken = $this->getIamTokenFromJwt();
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
     * Clear cached IAM token
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
     * Get Service Account ID
     *
     * @return string
     */
    public function getServiceAccountId(): string
    {
        return $this->serviceAccountId;
    }

    /**
     * Get cache key for IAM token
     *
     * @return string
     */
    private function getCacheKey(): string
    {
        return 'iam_token_sa_' . md5($this->serviceAccountId . $this->keyId);
    }
}
