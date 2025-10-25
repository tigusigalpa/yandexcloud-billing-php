<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Resources;

use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;

class BudgetResource extends AbstractResource
{
    /**
     * Получить информацию о бюджете
     *
     * @throws YandexCloudBillingException
     */
    public function get(string $budgetId): array
    {
        if (empty($budgetId)) {
            throw new YandexCloudBillingException('Budget ID cannot be empty');
        }

        return $this->makeRequest('GET', "budgets/{$budgetId}");
    }

    /**
     * Получить список бюджетов для биллингового аккаунта
     *
     * @throws YandexCloudBillingException
     */
    public function list(string $billingAccountId): array
    {
        if (empty($billingAccountId)) {
            throw new YandexCloudBillingException('Billing account ID cannot be empty');
        }

        return $this->makeRequest('GET', 'budgets', [
            'query' => [
                'billingAccountId' => $billingAccountId,
            ],
        ]);
    }

    /**
     * Создать бюджет
     *
     * @throws YandexCloudBillingException
     */
    public function create(array $budgetData): array
    {
        if (empty($budgetData)) {
            throw new YandexCloudBillingException('Budget data cannot be empty');
        }

        return $this->makeRequest('POST', 'budgets', [
            'json' => $budgetData,
        ]);
    }

    /**
     * Обновить бюджет
     *
     * @throws YandexCloudBillingException
     */
    public function update(string $budgetId, array $budgetData, ?string $updateMask = null): array
    {
        if (empty($budgetId)) {
            throw new YandexCloudBillingException('Budget ID cannot be empty');
        }

        if (empty($budgetData)) {
            throw new YandexCloudBillingException('Budget data cannot be empty');
        }

        $options = ['json' => $budgetData];

        if ($updateMask !== null) {
            $options['query'] = ['updateMask' => $updateMask];
        }

        return $this->makeRequest('PATCH', "budgets/{$budgetId}", $options);
    }

    /**
     * Удалить бюджет
     *
     * @throws YandexCloudBillingException
     */
    public function delete(string $budgetId): array
    {
        if (empty($budgetId)) {
            throw new YandexCloudBillingException('Budget ID cannot be empty');
        }

        return $this->makeRequest('DELETE', "budgets/{$budgetId}");
    }
}