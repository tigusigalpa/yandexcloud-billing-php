<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;

class ListAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'yandex-cloud:list-accounts 
                            {--format=table : Output format (table, json)}
                            {--with-clouds : Include bound clouds information}';

    /**
     * The console command description.
     */
    protected $description = 'List all billing accounts';

    /**
     * Execute the console command.
     */
    public function handle(YandexCloudBillingClient $client): int
    {
        try {
            $this->info('Fetching billing accounts...');
            $accounts = $client->billingAccount()->list();
            
            if (empty($accounts['billingAccounts'])) {
                $this->warn('No billing accounts found');
                return self::SUCCESS;
            }

            $format = $this->option('format');
            $withClouds = $this->option('with-clouds');

            if ($format === 'json') {
                $this->line(json_encode($accounts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                return self::SUCCESS;
            }

            // Table format
            $tableData = [];
            foreach ($accounts['billingAccounts'] as $account) {
                $row = [
                    'ID' => $account['id'],
                    'Name' => $account['name'] ?? 'N/A',
                    'Status' => $account['active'] ? 'Active' : 'Inactive',
                    'Country' => $account['countryCode'] ?? 'N/A',
                ];

                if ($withClouds) {
                    try {
                        $clouds = $client->billingAccount()->listBoundClouds($account['id']);
                        $cloudCount = count($clouds['cloudBindings'] ?? []);
                        $row['Clouds'] = $cloudCount;
                    } catch (YandexCloudBillingException $e) {
                        $row['Clouds'] = 'Error';
                    }
                }

                $tableData[] = $row;
            }

            $headers = array_keys($tableData[0]);
            $this->table($headers, $tableData);

            $this->info('Total accounts: ' . count($accounts['billingAccounts']));
            return self::SUCCESS;

        } catch (YandexCloudBillingException $e) {
            $this->error('API error: ' . $e->getMessage());
            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error('Unexpected error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
