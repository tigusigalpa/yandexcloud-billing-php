<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;

// Получите OAuth токен одним из способов:
// 1. Через Yandex Cloud CLI: yc iam create-token
// 2. Через веб-интерфейс: https://oauth.yandex.ru/authorize?response_type=token&client_id=1a6990aa636648e9b2ef855fa7bec2fb
// 3. Из переменной окружения
$oauthToken = $_ENV['YANDEX_OAUTH_TOKEN'] ?? 'your-oauth-token-here';

// Проверяем, что токен не является заглушкой
if ($oauthToken === 'your-oauth-token-here') {
    echo "❌ Ошибка: Необходимо указать реальный OAuth токен!\n";
    echo "Получите токен одним из способов:\n";
    echo "1. Yandex Cloud CLI: yc iam create-token\n";
    echo "2. Веб-интерфейс: https://oauth.yandex.ru/authorize?response_type=token&client_id=1a6990aa636648e9b2ef855fa7bec2fb\n";
    echo "3. Установите переменную окружения YANDEX_OAUTH_TOKEN\n";
    exit(1);
}

try {
    // Создаем клиент (теперь он автоматически получает IAM-токен из OAuth)
    $client = new YandexCloudBillingClient($oauthToken);

    echo "✅ Клиент успешно создан и готов к работе\n\n";

    // Получаем список биллинговых аккаунтов
    echo "📋 Получение списка биллинговых аккаунтов...\n";
    $accounts = $client->billingAccount()->list();
    echo "Найдено аккаунтов: " . count($accounts['billingAccounts'] ?? []) . "\n\n";

    if (!empty($accounts['billingAccounts'])) {
        $firstAccount = $accounts['billingAccounts'][0];
        $accountId = $firstAccount['id'];
        
        echo "🔍 Получение детальной информации об аккаунте: {$accountId}\n";
        $accountDetails = $client->billingAccount()->get($accountId);
        echo "Название: " . ($accountDetails['name'] ?? 'N/A') . "\n";
        echo "Статус: " . ($accountDetails['active'] ? 'Активен' : 'Неактивен') . "\n\n";

        // Получаем список привязанных облаков
        echo "☁️ Получение списка привязанных облаков...\n";
        $boundClouds = $client->billingAccount()->listBoundClouds($accountId);
        echo "Найдено облаков: " . count($boundClouds['cloudBindings'] ?? []) . "\n\n";

        // Получаем список бюджетов
        echo "💰 Получение списка бюджетов...\n";
        $budgets = $client->budget()->list($accountId);
        echo "Найдено бюджетов: " . count($budgets['budgets'] ?? []) . "\n\n";

        if (!empty($budgets['budgets'])) {
            $firstBudget = $budgets['budgets'][0];
            $budgetId = $firstBudget['id'];
            
            echo "📊 Получение детальной информации о бюджете: {$budgetId}\n";
            $budgetDetails = $client->budget()->get($budgetId);
            echo "Название: " . ($budgetDetails['name'] ?? 'N/A') . "\n";
            echo "Сумма: " . ($budgetDetails['amount'] ?? 'N/A') . "\n";
        }
    }

    echo "\n✅ Все операции выполнены успешно!\n";

} catch (AuthenticationException $e) {
    echo "❌ Ошибка аутентификации: " . $e->getMessage() . "\n";
    echo "💡 Проверьте правильность OAuth токена или получите новый:\n";
    echo "   yc iam create-token\n";
} catch (YandexCloudBillingException $e) {
    echo "❌ Ошибка API: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Неожиданная ошибка: " . $e->getMessage() . "\n";
}