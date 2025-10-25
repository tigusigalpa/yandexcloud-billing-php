<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Validators\BudgetValidator;
use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;
use Tigusigalpa\YandexCloudBilling\Exceptions\ValidationException;

// Получите OAuth токен (автоматически обменивается на IAM-токен)
$oauthToken = $_ENV['YANDEX_OAUTH_TOKEN'] ?? 'your-oauth-token-here';

if ($oauthToken === 'your-oauth-token-here') {
    echo "❌ Ошибка: Необходимо указать реальный OAuth токен!\n";
    echo "Получите токен: yc iam create-token\n";
    exit(1);
}

try {
    // Создаем клиент (теперь с автоматическим получением IAM-токена)
    $client = new YandexCloudBillingClient($oauthToken);
    $validator = new BudgetValidator();

    echo "✅ Клиент успешно инициализирован с IAM-аутентификацией\n\n";

    // Получаем первый доступный биллинговый аккаунт
    $accounts = $client->billingAccount()->list();
    if (empty($accounts['billingAccounts'])) {
        echo "❌ Не найдено биллинговых аккаунтов\n";
        exit(1);
    }

    $billingAccountId = $accounts['billingAccounts'][0]['id'];
    echo "📋 Используем биллинговый аккаунт: {$billingAccountId}\n\n";

    // === СОЗДАНИЕ БЮДЖЕТА ===
    echo "💰 Создание нового бюджета...\n";
    
    $budgetData = [
        'billingAccountId' => $billingAccountId,
        'name' => 'Test Budget ' . date('Y-m-d H:i:s'),
        'amount' => '1000.00',
        'resetPeriod' => 'MONTHLY',
        'thresholdRules' => [
            [
                'thresholdValue' => '800.00',
                'thresholdType' => 'PERCENT'
            ],
            [
                'thresholdValue' => '950.00', 
                'thresholdType' => 'PERCENT'
            ]
        ]
    ];

    // Валидация данных бюджета
    try {
        $validator->validateBudgetCreation($budgetData);
        echo "✅ Данные бюджета прошли валидацию\n";
    } catch (ValidationException $e) {
        echo "❌ Ошибка валидации: " . $e->getMessage() . "\n";
        exit(1);
    }

    // Создаем бюджет
    $createdBudget = $client->budget()->create($budgetData);
    $budgetId = $createdBudget['id'];
    echo "✅ Бюджет создан с ID: {$budgetId}\n\n";

    // === ПОЛУЧЕНИЕ ИНФОРМАЦИИ О БЮДЖЕТЕ ===
    echo "📊 Получение информации о созданном бюджете...\n";
    $budgetInfo = $client->budget()->get($budgetId);
    echo "Название: " . $budgetInfo['name'] . "\n";
    echo "Сумма: " . $budgetInfo['amount'] . "\n";
    echo "Период сброса: " . $budgetInfo['resetPeriod'] . "\n";
    echo "Количество правил: " . count($budgetInfo['thresholdRules']) . "\n\n";

    // === ОБНОВЛЕНИЕ БЮДЖЕТА ===
    echo "🔄 Обновление бюджета...\n";
    
    $updateData = [
        'name' => 'Updated Test Budget ' . date('Y-m-d H:i:s'),
        'amount' => '1500.00',
        'thresholdRules' => [
            [
                'thresholdValue' => '75.00',
                'thresholdType' => 'PERCENT'
            ]
        ]
    ];

    // Валидация данных для обновления
    try {
        $validator->validateBudgetUpdate($updateData);
        echo "✅ Данные для обновления прошли валидацию\n";
    } catch (ValidationException $e) {
        echo "❌ Ошибка валидации обновления: " . $e->getMessage() . "\n";
    }

    $updatedBudget = $client->budget()->update($budgetId, $updateData);
    echo "✅ Бюджет обновлен\n";
    echo "Новое название: " . $updatedBudget['name'] . "\n";
    echo "Новая сумма: " . $updatedBudget['amount'] . "\n\n";

    // === ПОЛУЧЕНИЕ СПИСКА ВСЕХ БЮДЖЕТОВ ===
    echo "📋 Получение списка всех бюджетов аккаунта...\n";
    $budgets = $client->budget()->list($billingAccountId);
    echo "Всего бюджетов: " . count($budgets['budgets']) . "\n";
    
    foreach ($budgets['budgets'] as $budget) {
        echo "- {$budget['name']} (ID: {$budget['id']}, Сумма: {$budget['amount']})\n";
    }

    // === УДАЛЕНИЕ БЮДЖЕТА (опционально) ===
    echo "\n🗑️ Удаление тестового бюджета...\n";
    $client->budget()->delete($budgetId);
    echo "✅ Бюджет удален\n";

    echo "\n🎉 Все операции с бюджетами выполнены успешно!\n";

} catch (AuthenticationException $e) {
    echo "❌ Ошибка аутентификации: " . $e->getMessage() . "\n";
    echo "💡 Проверьте OAuth токен или получите новый: yc iam create-token\n";
} catch (ValidationException $e) {
    echo "❌ Ошибка валидации: " . $e->getMessage() . "\n";
} catch (YandexCloudBillingException $e) {
    echo "❌ Ошибка API: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Неожиданная ошибка: " . $e->getMessage() . "\n";
}