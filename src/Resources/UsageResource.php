<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Resources;

use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;

class UsageResource extends AbstractResource
{
    /**
     * Получить данные об использовании ресурсов
     *
     * @param string $billingAccountId
     * @param array $filters
     * @throws YandexCloudBillingException
     */
    public function get(string $billingAccountId, array $filters = []): array
    {
        if (empty($billingAccountId)) {
            throw new YandexCloudBillingException('Billing account ID cannot be empty');
        }

        $queryParams = array_merge([
            'billingAccountId' => $billingAccountId,
        ], $filters);

        return $this->makeRequest('GET', 'usage', [
            'query' => $queryParams,
        ]);
    }

    /**
     * Получить детальную информацию об использовании ресурсов
     *
     * @param string $billingAccountId
     * @param string $startTime RFC3339 timestamp
     * @param string $endTime RFC3339 timestamp
     * @param array $filters
     * @throws YandexCloudBillingException
     */
    public function getDetailed(string $billingAccountId, string $startTime, string $endTime, array $filters = []): array
    {
        if (empty($billingAccountId)) {
            throw new YandexCloudBillingException('Billing account ID cannot be empty');
        }

        if (empty($startTime) || empty($endTime)) {
            throw new YandexCloudBillingException('Start time and end time are required');
        }

        $queryParams = array_merge([
            'billingAccountId' => $billingAccountId,
            'startTime' => $startTime,
            'endTime' => $endTime,
        ], $filters);

        return $this->makeRequest('GET', 'usage/detailed', [
            'query' => $queryParams,
        ]);
    }

    /**
     * Получить агрегированные данные об использовании
     *
     * @param string $billingAccountId
     * @param string $aggregation Тип агрегации (DAILY, MONTHLY)
     * @param array $filters
     * @throws YandexCloudBillingException
     */
    public function getAggregated(string $billingAccountId, string $aggregation = 'DAILY', array $filters = []): array
    {
        if (empty($billingAccountId)) {
            throw new YandexCloudBillingException('Billing account ID cannot be empty');
        }

        if (!in_array($aggregation, ['DAILY', 'MONTHLY'])) {
            throw new YandexCloudBillingException('Aggregation must be DAILY or MONTHLY');
        }

        $queryParams = array_merge([
            'billingAccountId' => $billingAccountId,
            'aggregation' => $aggregation,
        ], $filters);

        return $this->makeRequest('GET', 'usage/aggregated', [
            'query' => $queryParams,
        ]);
    }

    /**
     * Экспортировать данные об использовании
     *
     * @param string $billingAccountId
     * @param string $format Формат экспорта (CSV, JSON)
     * @param array $filters
     * @throws YandexCloudBillingException
     */
    public function export(string $billingAccountId, string $format = 'CSV', array $filters = []): array
    {
        if (empty($billingAccountId)) {
            throw new YandexCloudBillingException('Billing account ID cannot be empty');
        }

        if (!in_array($format, ['CSV', 'JSON'])) {
            throw new YandexCloudBillingException('Format must be CSV or JSON');
        }

        $data = array_merge([
            'billingAccountId' => $billingAccountId,
            'format' => $format,
        ], $filters);

        return $this->makeRequest('POST', 'usage/export', [
            'json' => $data,
        ]);
    }
}
