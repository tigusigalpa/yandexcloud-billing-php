<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Resources;

use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;

class CostResource extends AbstractResource
{
    /**
     * Получить информацию о стоимости
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

        return $this->makeRequest('GET', 'cost', [
            'query' => $queryParams,
        ]);
    }

    /**
     * Получить прогноз стоимости
     *
     * @param string $billingAccountId
     * @param string $forecastPeriod Период прогноза (MONTH, QUARTER)
     * @param array $filters
     * @throws YandexCloudBillingException
     */
    public function getForecast(string $billingAccountId, string $forecastPeriod = 'MONTH', array $filters = []): array
    {
        if (empty($billingAccountId)) {
            throw new YandexCloudBillingException('Billing account ID cannot be empty');
        }

        if (!in_array($forecastPeriod, ['MONTH', 'QUARTER'])) {
            throw new YandexCloudBillingException('Forecast period must be MONTH or QUARTER');
        }

        $queryParams = array_merge([
            'billingAccountId' => $billingAccountId,
            'forecastPeriod' => $forecastPeriod,
        ], $filters);

        return $this->makeRequest('GET', 'cost/forecast', [
            'query' => $queryParams,
        ]);
    }

    /**
     * Получить разбивку стоимости по сервисам
     *
     * @param string $billingAccountId
     * @param string $startTime RFC3339 timestamp
     * @param string $endTime RFC3339 timestamp
     * @param array $filters
     * @throws YandexCloudBillingException
     */
    public function getByServices(string $billingAccountId, string $startTime, string $endTime, array $filters = []): array
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

        return $this->makeRequest('GET', 'cost/services', [
            'query' => $queryParams,
        ]);
    }

    /**
     * Получить разбивку стоимости по ресурсам
     *
     * @param string $billingAccountId
     * @param string $startTime RFC3339 timestamp
     * @param string $endTime RFC3339 timestamp
     * @param array $filters
     * @throws YandexCloudBillingException
     */
    public function getByResources(string $billingAccountId, string $startTime, string $endTime, array $filters = []): array
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

        return $this->makeRequest('GET', 'cost/resources', [
            'query' => $queryParams,
        ]);
    }

    /**
     * Получить тренды стоимости
     *
     * @param string $billingAccountId
     * @param string $period Период анализа (WEEK, MONTH, QUARTER)
     * @param array $filters
     * @throws YandexCloudBillingException
     */
    public function getTrends(string $billingAccountId, string $period = 'MONTH', array $filters = []): array
    {
        if (empty($billingAccountId)) {
            throw new YandexCloudBillingException('Billing account ID cannot be empty');
        }

        if (!in_array($period, ['WEEK', 'MONTH', 'QUARTER'])) {
            throw new YandexCloudBillingException('Period must be WEEK, MONTH, or QUARTER');
        }

        $queryParams = array_merge([
            'billingAccountId' => $billingAccountId,
            'period' => $period,
        ], $filters);

        return $this->makeRequest('GET', 'cost/trends', [
            'query' => $queryParams,
        ]);
    }
}
