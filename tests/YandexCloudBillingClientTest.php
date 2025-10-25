<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Tigusigalpa\YandexCloudBilling\Auth\IamTokenManager;
use Tigusigalpa\YandexCloudBilling\Resources\BillingAccountResource;
use Tigusigalpa\YandexCloudBilling\Resources\BudgetResource;
use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;

class YandexCloudBillingClientTest extends TestCase
{
    private YandexCloudBillingClient $client;

    protected function setUp(): void
    {
        $this->client = new YandexCloudBillingClient('test-oauth-token');
    }

    public function testClientCreation(): void
    {
        $this->assertInstanceOf(YandexCloudBillingClient::class, $this->client);
        $this->assertEquals('test-oauth-token', $this->client->getOauthToken());
    }

    public function testClientWithCustomHttpClient(): void
    {
        $httpClient = new Client();
        $client = new YandexCloudBillingClient('test-oauth-token', $httpClient);
        
        $this->assertSame($httpClient, $client->getHttpClient());
    }

    public function testClientHasIamTokenManager(): void
    {
        $iamTokenManager = $this->client->getIamTokenManager();
        $this->assertInstanceOf(IamTokenManager::class, $iamTokenManager);
    }

    public function testEmptyOauthTokenThrowsException(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('OAuth токен не может быть пустым');
        
        new YandexCloudBillingClient('');
    }

    public function testBillingAccountResource(): void
    {
        $resource = $this->client->billingAccount();
        $this->assertInstanceOf(BillingAccountResource::class, $resource);
    }

    public function testBudgetResource(): void
    {
        $resource = $this->client->budget();
        $this->assertInstanceOf(BudgetResource::class, $resource);
    }

    public function testConstantsAreDefined(): void
    {
        $this->assertIsString(YandexCloudBillingClient::BASE_URL);
        $this->assertIsString(YandexCloudBillingClient::IAM_TOKEN_ENDPOINT);
        $this->assertIsString(YandexCloudBillingClient::RESOURCE_MANAGER_ENDPOINT);
    }
}