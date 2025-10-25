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
use Tigusigalpa\YandexCloudBilling\Resources\BudgetResource;

class BudgetResourceTest extends TestCase
{
    private BudgetResource $resource;
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
        
        $this->resource = new BudgetResource($client, $this->iamTokenManager);
    }

    public function testList(): void
    {
        $expectedResponse = [
            'budgets' => [
                ['id' => 'budget1', 'name' => 'Test Budget 1'],
                ['id' => 'budget2', 'name' => 'Test Budget 2'],
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->resource->list('billing-account-id');
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGet(): void
    {
        $expectedResponse = [
            'id' => 'budget1',
            'name' => 'Test Budget',
            'amount' => '1000.00'
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->resource->get('budget1');
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGetWithEmptyId(): void
    {
        $this->expectException(YandexCloudBillingException::class);
        $this->expectExceptionMessage('Budget ID cannot be empty');

        $this->resource->get('');
    }

    public function testCreate(): void
    {
        $budgetData = [
            'billingAccountId' => 'billing-account-id',
            'name' => 'New Budget',
            'amount' => '1000.00'
        ];

        $expectedResponse = [
            'id' => 'new-budget-id',
            'name' => 'New Budget',
            'amount' => '1000.00'
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->resource->create($budgetData);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testUpdate(): void
    {
        $updateData = [
            'name' => 'Updated Budget',
            'amount' => '2000.00'
        ];

        $expectedResponse = [
            'id' => 'budget1',
            'name' => 'Updated Budget',
            'amount' => '2000.00'
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->resource->update('budget1', $updateData);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testDelete(): void
    {
        $expectedResponse = ['operation' => ['id' => 'op123', 'done' => true]];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $result = $this->resource->delete('budget1');
        $this->assertEquals($expectedResponse, $result);
    }
}