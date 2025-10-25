<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Tests\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Tigusigalpa\YandexCloudBilling\Auth\IamTokenManager;
use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;
use Tigusigalpa\YandexCloudBilling\Resources\BillingAccountResource;

class BillingAccountResourceTest extends TestCase
{
    private BillingAccountResource $resource;
    private MockHandler $mockHandler;
    private IamTokenManager $iamTokenManager;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handlerStack]);
        
        // Создаем мок для IamTokenManager
        $this->iamTokenManager = $this->createMock(IamTokenManager::class);
        $this->iamTokenManager->method('getValidIamToken')->willReturn('test-iam-token');
        
        $this->resource = new BillingAccountResource($client, $this->iamTokenManager);
    }

    public function testList(): void
    {
        $expectedResponse = [
            'billingAccounts' => [
                ['id' => 'account1', 'name' => 'Test Account 1'],
                ['id' => 'account2', 'name' => 'Test Account 2'],
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->resource->list();
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGet(): void
    {
        $expectedResponse = [
            'id' => 'account1',
            'name' => 'Test Account',
            'active' => true
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->resource->get('account1');
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGetWithEmptyId(): void
    {
        $this->expectException(YandexCloudBillingException::class);
        $this->expectExceptionMessage('Billing account ID cannot be empty');

        $this->resource->get('');
    }

    public function testListBoundClouds(): void
    {
        $expectedResponse = [
            'cloudBindings' => [
                ['cloudId' => 'cloud1', 'name' => 'Test Cloud 1'],
                ['cloudId' => 'cloud2', 'name' => 'Test Cloud 2'],
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->resource->listBoundClouds('account1');
        $this->assertEquals($expectedResponse, $result);
    }

    public function testBindBillableObject(): void
    {
        $expectedResponse = ['operation' => ['id' => 'op123', 'done' => true]];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->resource->bindBillableObject('account1', 'cloud1');
        $this->assertEquals($expectedResponse, $result);
    }
}