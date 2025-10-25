<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Tests\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Tigusigalpa\YandexCloudBilling\Auth\IamTokenManager;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;

class IamTokenManagerTest extends TestCase
{
    private string $oauthToken = 'test-oauth-token';

    public function testGetValidIamTokenFirstTime(): void
    {
        $mockResponse = new Response(200, [], json_encode([
            'iamToken' => 'test-iam-token',
            'expiresAt' => (new \DateTime('+12 hours'))->format('c')
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $manager = new IamTokenManager($this->oauthToken, $httpClient);
        $iamToken = $manager->getValidIamToken();

        $this->assertEquals('test-iam-token', $iamToken);
    }

    public function testGetValidIamTokenFromCache(): void
    {
        $mockResponse = new Response(200, [], json_encode([
            'iamToken' => 'test-iam-token',
            'expiresAt' => (new \DateTime('+12 hours'))->format('c')
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $manager = new IamTokenManager($this->oauthToken, $httpClient);
        
        // Первый вызов - получение токена
        $firstToken = $manager->getValidIamToken();
        
        // Второй вызов - должен вернуть кэшированный токен (без нового HTTP запроса)
        $secondToken = $manager->getValidIamToken();

        $this->assertEquals($firstToken, $secondToken);
        $this->assertEquals('test-iam-token', $secondToken);
    }

    public function testGetValidIamTokenRefreshExpired(): void
    {
        // Первый ответ - токен с истекшим сроком
        $expiredResponse = new Response(200, [], json_encode([
            'iamToken' => 'expired-token',
            'expiresAt' => (new \DateTime('-1 hour'))->format('c')
        ]));

        // Второй ответ - новый токен
        $newResponse = new Response(200, [], json_encode([
            'iamToken' => 'new-iam-token',
            'expiresAt' => (new \DateTime('+12 hours'))->format('c')
        ]));

        $mock = new MockHandler([$expiredResponse, $newResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $manager = new IamTokenManager($this->oauthToken, $httpClient);
        
        // Первый вызов - получение истекшего токена
        $manager->getValidIamToken();
        
        // Второй вызов - должен обновить токен
        $newToken = $manager->getValidIamToken();

        $this->assertEquals('new-iam-token', $newToken);
    }

    public function testGetIamTokenApiError(): void
    {
        $errorResponse = new Response(401, [], json_encode([
            'error' => 'invalid_grant',
            'error_description' => 'Invalid OAuth token'
        ]));

        $mock = new MockHandler([$errorResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $manager = new IamTokenManager($this->oauthToken, $httpClient);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Ошибка получения IAM токена: Invalid OAuth token');

        $manager->getValidIamToken();
    }

    public function testGetIamTokenNetworkError(): void
    {
        $mock = new MockHandler([
            new \GuzzleHttp\Exception\ConnectException('Connection failed', new \GuzzleHttp\Psr7\Request('POST', 'test'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $manager = new IamTokenManager($this->oauthToken, $httpClient);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Ошибка сети при получении IAM токена');

        $manager->getValidIamToken();
    }

    public function testGetOauthToken(): void
    {
        $manager = new IamTokenManager($this->oauthToken);
        $this->assertEquals($this->oauthToken, $manager->getOauthToken());
    }
}