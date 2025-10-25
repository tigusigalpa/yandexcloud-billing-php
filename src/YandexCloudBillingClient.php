<?php

declare(strict_types = 1);

namespace Tigusigalpa\YandexCloudBilling;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Tigusigalpa\YandexCloudBilling\Auth\IamTokenManager;
use Tigusigalpa\YandexCloudBilling\Auth\ServiceAccountAuth;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;
use Tigusigalpa\YandexCloudBilling\Resources\BillingAccountResource;
use Tigusigalpa\YandexCloudBilling\Resources\BudgetResource;
use Tigusigalpa\YandexCloudBilling\Resources\UsageResource;
use Tigusigalpa\YandexCloudBilling\Resources\CostResource;

class YandexCloudBillingClient
{
    private const BASE_URI = 'https://billing.api.cloud.yandex.net/billing/v1/';
    public const IAM_TOKEN_ENDPOINT = 'https://iam.api.cloud.yandex.net/iam/v1/tokens';
    public const RESOURCE_MANAGER_ENDPOINT = 'https://resource-manager.api.cloud.yandex.net/resource-manager/v1/';

    private ClientInterface $httpClient;
    private IamTokenManager|ServiceAccountAuth $authManager;

    public function __construct(string $oauthToken, ?ClientInterface $httpClient = null)
    {
        if (empty($oauthToken)) {
            throw new AuthenticationException('OAuth token cannot be empty');
        }

        $this->authManager = new IamTokenManager($oauthToken);
        
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => self::BASE_URI,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Create client with Service Account authentication
     */
    public static function createWithServiceAccount(
        string $serviceAccountId,
        string $keyId,
        string $privateKey,
        ?ClientInterface $httpClient = null
    ): self {
        $instance = new self('dummy-token'); // Временный токен для конструктора
        $instance->authManager = new ServiceAccountAuth($serviceAccountId, $keyId, $privateKey);
        
        $instance->httpClient = $httpClient ?? new Client([
            'base_uri' => self::BASE_URI,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        return $instance;
    }

    public function billingAccount(): BillingAccountResource
    {
        return new BillingAccountResource($this->httpClient, $this->authManager);
    }

    public function budget(): BudgetResource
    {
        return new BudgetResource($this->httpClient, $this->authManager);
    }

    public function usage(): UsageResource
    {
        return new UsageResource($this->httpClient, $this->authManager);
    }

    public function cost(): CostResource
    {
        return new CostResource($this->httpClient, $this->authManager);
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    public function getAuthManager(): IamTokenManager|ServiceAccountAuth
    {
        return $this->authManager;
    }

    public function getOauthToken(): string
    {
        if ($this->authManager instanceof IamTokenManager) {
            return $this->authManager->getOAuthToken();
        }
        
        throw new AuthenticationException('OAuth token is not available for Service Account authentication');
    }
}
