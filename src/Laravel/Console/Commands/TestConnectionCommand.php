<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;

class TestConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'yandex-cloud:test-connection';

    /**
     * The console command description.
     */
    protected $description = 'Test connection to Yandex Cloud Billing API';

    /**
     * Execute the console command.
     */
    public function handle(YandexCloudBillingClient $client): int
    {
        $this->info('Testing connection to Yandex Cloud Billing API...');

        try {
            // Проверяем аутентификацию
            $this->line('🔐 Testing authentication...');
            $authManager = $client->getAuthManager();
            
            if ($authManager->hasValidCachedToken()) {
                $this->info('✅ Using cached IAM token');
            } else {
                $this->line('🔄 Obtaining new IAM token...');
            }

            // Получаем список биллинговых аккаунтов
            $this->line('📋 Fetching billing accounts...');
            $accounts = $client->billingAccount()->list();
            
            $accountCount = count($accounts['billingAccounts'] ?? []);
            $this->info("✅ Found {$accountCount} billing account(s)");

            if ($accountCount > 0) {
                $this->table(
                    ['ID', 'Name', 'Status'],
                    collect($accounts['billingAccounts'])->map(function ($account) {
                        return [
                            $account['id'],
                            $account['name'] ?? 'N/A',
                            $account['active'] ? 'Active' : 'Inactive',
                        ];
                    })->toArray()
                );

                // Тестируем получение бюджетов для первого аккаунта
                $firstAccount = $accounts['billingAccounts'][0];
                $this->line("💰 Fetching budgets for account: {$firstAccount['id']}");
                
                $budgets = $client->budget()->list($firstAccount['id']);
                $budgetCount = count($budgets['budgets'] ?? []);
                $this->info("✅ Found {$budgetCount} budget(s)");
            }

            $this->info('🎉 Connection test completed successfully!');
            return self::SUCCESS;

        } catch (AuthenticationException $e) {
            $this->error('❌ Authentication failed: ' . $e->getMessage());
            $this->line('💡 Check your OAuth token or Service Account credentials');
            return self::FAILURE;
        } catch (YandexCloudBillingException $e) {
            $this->error('❌ API error: ' . $e->getMessage());
            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error('❌ Unexpected error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
