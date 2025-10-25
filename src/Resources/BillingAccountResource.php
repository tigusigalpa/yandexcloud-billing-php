<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Resources;

use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;

class BillingAccountResource extends AbstractResource
{
    /**
     * Получить список биллинговых аккаунтов
     *
     * @throws YandexCloudBillingException
     */
    public function list(): array
    {
        return $this->makeRequest('GET', 'billingAccounts');
    }

    /**
     * Получить информацию о биллинговом аккаунте
     *
     * @throws YandexCloudBillingException
     */
    public function get(string $billingAccountId): array
    {
        if (empty($billingAccountId)) {
            throw new YandexCloudBillingException('Billing account ID cannot be empty');
        }

        return $this->makeRequest('GET', "billingAccounts/{$billingAccountId}");
    }

    /**
     * Получить список привязанных облаков к биллинговому аккаунту
     *
     * @throws YandexCloudBillingException
     */
    public function listBoundClouds(string $billingAccountId): array
    {
        if (empty($billingAccountId)) {
            throw new YandexCloudBillingException('Billing account ID cannot be empty');
        }

        return $this->makeRequest('GET', "billingAccounts/{$billingAccountId}:listBoundClouds");
    }

    /**
     * Привязать облако к биллинговому аккаунту
     *
     * @throws YandexCloudBillingException
     */
    public function bindBillableObject(string $billingAccountId, string $billableObjectId, string $billableObjectType = 'cloud'): array
    {
        if (empty($billingAccountId)) {
            throw new YandexCloudBillingException('Billing account ID cannot be empty');
        }

        if (empty($billableObjectId)) {
            throw new YandexCloudBillingException('Billable object ID cannot be empty');
        }

        $data = [
            'billableObject' => [
                'id' => $billableObjectId,
                'type' => $billableObjectType,
            ],
        ];

        return $this->makeRequest('POST', "billingAccounts/{$billingAccountId}:bindBillableObject", [
            'json' => $data,
        ]);
    }

    /**
     * Отвязать облако от биллингового аккаунта
     *
     * @throws YandexCloudBillingException
     */
    public function listAccessBindings(string $billingAccountId): array
    {
        if (empty($billingAccountId)) {
            throw new YandexCloudBillingException('Billing account ID cannot be empty');
        }

        return $this->makeRequest('GET', "billingAccounts/{$billingAccountId}:listAccessBindings");
    }
}