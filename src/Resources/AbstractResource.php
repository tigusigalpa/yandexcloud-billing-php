<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Resources;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Tigusigalpa\YandexCloudClient\Auth\IamTokenManager;
use Tigusigalpa\YandexCloudClient\Auth\ServiceAccountAuth;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;
use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;

abstract class AbstractResource
{
    protected ClientInterface $httpClient;
    protected IamTokenManager|ServiceAccountAuth $authManager;

    public function __construct(ClientInterface $httpClient, IamTokenManager|ServiceAccountAuth $authManager)
    {
        $this->httpClient = $httpClient;
        $this->authManager = $authManager;
    }

    /**
     * @throws YandexCloudBillingException
     * @throws AuthenticationException
     */
    protected function makeRequest(string $method, string $uri, array $options = []): array
    {
        try {
            // Получаем актуальный IAM-токен
            $iamToken = $this->authManager->getValidIamToken();
            
            // Добавляем заголовок авторизации к опциям запроса
            $options['headers'] = array_merge(
                $options['headers'] ?? [],
                ['Authorization' => 'Bearer ' . $iamToken]
            );
            
            $response = $this->httpClient->request($method, $uri, $options);
            return $this->parseResponse($response);
        } catch (AuthenticationException $e) {
            throw $e;
        } catch (GuzzleException $e) {
            throw new YandexCloudBillingException(
                'HTTP request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @throws YandexCloudBillingException
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $body = $response->getBody()->getContents();
        
        if ($response->getStatusCode() >= 400) {
            throw new YandexCloudBillingException(
                'API request failed with status ' . $response->getStatusCode() . ': ' . $body,
                $response->getStatusCode()
            );
        }

        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new YandexCloudBillingException(
                'Failed to parse JSON response: ' . json_last_error_msg()
            );
        }

        return $data ?? [];
    }
}