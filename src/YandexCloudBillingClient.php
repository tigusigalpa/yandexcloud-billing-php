<?php

declare(strict_types = 1);

namespace Tigusigalpa\YandexCloudBilling;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Tigusigalpa\YandexCloudClient\YandexCloudClient;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;
use Tigusigalpa\YandexCloudBilling\Resources\BillingAccountResource;
use Tigusigalpa\YandexCloudBilling\Resources\BudgetResource;
use Tigusigalpa\YandexCloudBilling\Resources\UsageResource;
use Tigusigalpa\YandexCloudBilling\Resources\CostResource;

class YandexCloudBillingClient
{
    private const BASE_URI = 'https://billing.api.cloud.yandex.net/billing/v1/';

    private ClientInterface $httpClient;
    private YandexCloudClient $cloudClient;

    public function __construct(string $oauthToken, ?ClientInterface $httpClient = null)
    {
        if (empty($oauthToken)) {
            throw new AuthenticationException('OAuth token cannot be empty');
        }

        $this->cloudClient = new YandexCloudClient($oauthToken, $httpClient);
        
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
        $instance->cloudClient = YandexCloudClient::createWithServiceAccount(
            $serviceAccountId,
            $keyId,
            $privateKey,
            $httpClient
        );
        
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
        return new BillingAccountResource($this->httpClient, $this->cloudClient->getAuthManager());
    }

    public function budget(): BudgetResource
    {
        return new BudgetResource($this->httpClient, $this->cloudClient->getAuthManager());
    }

    public function usage(): UsageResource
    {
        return new UsageResource($this->httpClient, $this->cloudClient->getAuthManager());
    }

    public function cost(): CostResource
    {
        return new CostResource($this->httpClient, $this->cloudClient->getAuthManager());
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Get Yandex Cloud Client for cloud resource management
     *
     * @return YandexCloudClient
     */
    public function getCloudClient(): YandexCloudClient
    {
        return $this->cloudClient;
    }
}
