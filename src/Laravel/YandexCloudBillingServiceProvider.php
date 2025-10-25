<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;

class YandexCloudBillingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/yandex-cloud-billing.php',
            'yandex-cloud-billing'
        );

        $this->app->singleton(YandexCloudBillingClient::class, function (Application $app) {
            $config = $app['config']['yandex-cloud-billing'];

            // Проверяем тип аутентификации
            if ($config['auth_type'] === 'service_account') {
                return YandexCloudBillingClient::createWithServiceAccount(
                    $config['service_account']['id'],
                    $config['service_account']['key_id'],
                    $config['service_account']['private_key']
                );
            }

            // OAuth аутентификация по умолчанию
            return new YandexCloudBillingClient($config['oauth_token']);
        });

        $this->app->alias(YandexCloudBillingClient::class, 'yandex-cloud-billing');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/yandex-cloud-billing.php' => config_path('yandex-cloud-billing.php'),
            ], 'yandex-cloud-billing-config');

            $this->commands([
                Console\Commands\TestConnectionCommand::class,
                Console\Commands\ListAccountsCommand::class,
                Console\Commands\CreateBudgetCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            YandexCloudBillingClient::class,
            'yandex-cloud-billing',
        ];
    }
}
