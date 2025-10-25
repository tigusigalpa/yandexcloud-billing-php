<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;

/**
 * @method static \Tigusigalpa\YandexCloudBilling\Resources\BillingAccountResource billingAccount()
 * @method static \Tigusigalpa\YandexCloudBilling\Resources\BudgetResource budget()
 * @method static \GuzzleHttp\ClientInterface getHttpClient()
 * @method static \Tigusigalpa\YandexCloudBilling\Auth\IamTokenManager|\Tigusigalpa\YandexCloudBilling\Auth\ServiceAccountAuth getAuthManager()
 * @method static string getOauthToken()
 *
 * @see \Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient
 */
class YandexCloudBilling extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return YandexCloudBillingClient::class;
    }
}
