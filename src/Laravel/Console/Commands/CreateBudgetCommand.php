<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Validators\BudgetValidator;
use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;
use Tigusigalpa\YandexCloudBilling\Exceptions\ValidationException;

class CreateBudgetCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'yandex-cloud:create-budget
                            {account-id : Billing account ID}
                            {name : Budget name}
                            {amount : Budget amount}
                            {--period=MONTHLY : Reset period (MONTHLY, QUARTERLY, ANNUALLY)}
                            {--threshold=80 : Threshold percentage for notifications}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new budget for billing account';

    /**
     * Execute the console command.
     */
    public function handle(YandexCloudBillingClient $client): int
    {
        try {
            $accountId = $this->argument('account-id');
            $name = $this->argument('name');
            $amount = $this->argument('amount');
            $period = $this->option('period');
            $threshold = (int) $this->option('threshold');

            // Валидация входных данных
            if (!is_numeric($amount) || $amount <= 0) {
                $this->error('Amount must be a positive number');
                return self::FAILURE;
            }

            if (!in_array($period, ['MONTHLY', 'QUARTERLY', 'ANNUALLY'])) {
                $this->error('Period must be one of: MONTHLY, QUARTERLY, ANNUALLY');
                return self::FAILURE;
            }

            if ($threshold <= 0 || $threshold > 100) {
                $this->error('Threshold must be between 1 and 100');
                return self::FAILURE;
            }

            // Проверяем существование биллингового аккаунта
            $this->line('Checking billing account...');
            try {
                $account = $client->billingAccount()->get($accountId);
                $this->info("✅ Account found: {$account['name']}");
            } catch (YandexCloudBillingException $e) {
                $this->error("❌ Billing account not found: {$accountId}");
                return self::FAILURE;
            }

            // Подготавливаем данные бюджета
            $budgetData = [
                'billingAccountId' => $accountId,
                'name' => $name,
                'amount' => (string) $amount,
                'resetPeriod' => $period,
                'thresholdRules' => [
                    [
                        'thresholdValue' => $threshold,
                        'thresholdType' => 'PERCENT',
                        'sendNotificationsToIds' => [], // Можно добавить ID пользователей
                    ],
                ],
            ];

            // Валидация данных
            $this->line('Validating budget data...');
            BudgetValidator::validateCreateData($budgetData);
            $this->info('✅ Validation passed');

            // Создаем бюджет
            $this->line('Creating budget...');
            $result = $client->budget()->create($budgetData);

            $this->info('🎉 Budget created successfully!');
            $this->table(
                ['Property', 'Value'],
                [
                    ['ID', $result['id'] ?? 'N/A'],
                    ['Name', $result['name'] ?? 'N/A'],
                    ['Amount', $result['amount'] ?? 'N/A'],
                    ['Period', $result['resetPeriod'] ?? 'N/A'],
                    ['Status', $result['status'] ?? 'N/A'],
                ]
            );

            return self::SUCCESS;

        } catch (ValidationException $e) {
            $this->error('❌ Validation error: ' . $e->getMessage());
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
