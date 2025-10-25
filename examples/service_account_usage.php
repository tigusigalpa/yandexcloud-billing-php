<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Cache\FileCache;
use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;

// Настройки Service Account
$serviceAccountId = $_ENV['YANDEX_SERVICE_ACCOUNT_ID'] ?? 'your-service-account-id';
$keyId = $_ENV['YANDEX_SERVICE_ACCOUNT_KEY_ID'] ?? 'your-key-id';
$privateKey = $_ENV['YANDEX_SERVICE_ACCOUNT_PRIVATE_KEY'] ?? 'your-private-key';

// Проверяем, что настройки не являются заглушками
if ($serviceAccountId === 'your-service-account-id' || 
    $keyId === 'your-key-id' || 
    $privateKey === 'your-private-key') {
    echo "❌ Ошибка: Необходимо указать реальные данные Service Account!\n";
    echo "Установите переменные окружения:\n";
    echo "- YANDEX_SERVICE_ACCOUNT_ID\n";
    echo "- YANDEX_SERVICE_ACCOUNT_KEY_ID\n";
    echo "- YANDEX_SERVICE_ACCOUNT_PRIVATE_KEY\n";
    exit(1);
}

try {
    // Создаем кэш для токенов
    $cache = new FileCache();
    
    // Создаем клиент с Service Account аутентификацией
    $client = YandexCloudBillingClient::createWithServiceAccount(
        $serviceAccountId,
        $keyId,
        $privateKey
    );

    echo "✅ Клиент с Service Account создан успешно\n\n";

    // Получаем список биллинговых аккаунтов
    echo "📋 Получение списка биллинговых аккаунтов...\n";
    $accounts = $client->billingAccount()->list();
    echo "Найдено аккаунтов: " . count($accounts['billingAccounts'] ?? []) . "\n\n";

    if (!empty($accounts['billingAccounts'])) {
        $firstAccount = $accounts['billingAccounts'][0];
        $accountId = $firstAccount['id'];
        
        echo "🔍 Работа с аккаунтом: {$accountId}\n";
        
        // Получаем данные об использовании
        echo "📊 Получение данных об использовании...\n";
        try {
            $usage = $client->usage()->get($accountId, [
                'startTime' => date('c', strtotime('-30 days')),
                'endTime' => date('c'),
            ]);
            echo "✅ Данные об использовании получены\n";
        } catch (YandexCloudBillingException $e) {
            echo "⚠️ Данные об использовании недоступны: " . $e->getMessage() . "\n";
        }

        // Получаем информацию о стоимости
        echo "💰 Получение информации о стоимости...\n";
        try {
            $cost = $client->cost()->get($accountId, [
                'startTime' => date('c', strtotime('-30 days')),
                'endTime' => date('c'),
            ]);
            echo "✅ Информация о стоимости получена\n";
        } catch (YandexCloudBillingException $e) {
            echo "⚠️ Информация о стоимости недоступна: " . $e->getMessage() . "\n";
        }

        // Получаем прогноз стоимости
        echo "🔮 Получение прогноза стоимости...\n";
        try {
            $forecast = $client->cost()->getForecast($accountId, 'MONTH');
            echo "✅ Прогноз стоимости получен\n";
        } catch (YandexCloudBillingException $e) {
            echo "⚠️ Прогноз стоимости недоступен: " . $e->getMessage() . "\n";
        }

        // Получаем список бюджетов
        echo "📈 Получение списка бюджетов...\n";
        $budgets = $client->budget()->list($accountId);
        echo "Найдено бюджетов: " . count($budgets['budgets'] ?? []) . "\n";
    }

    echo "\n✅ Все операции с Service Account выполнены успешно!\n";

} catch (AuthenticationException $e) {
    echo "❌ Ошибка аутентификации: " . $e->getMessage() . "\n";
    echo "💡 Проверьте правильность данных Service Account\n";
} catch (YandexCloudBillingException $e) {
    echo "❌ Ошибка API: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Неожиданная ошибка: " . $e->getMessage() . "\n";
}
