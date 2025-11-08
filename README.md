# Yandex Cloud Billing PHP SDK

![Yandex Cloud Billing PHP SDK](https://github.com/user-attachments/assets/3f8a22f8-2f47-4a0a-8198-b072cef361b3)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tigusigalpa/yandexcloud-billing-php.svg?style=flat-square)](https://packagist.org/packages/tigusigalpa/yandexcloud-billing-php)
[![PHP Version](https://img.shields.io/packagist/php-v/tigusigalpa/yandexcloud-billing-php.svg?style=flat-square)](https://packagist.org/packages/tigusigalpa/yandexcloud-billing-php)
[![License](https://img.shields.io/packagist/l/tigusigalpa/yandexcloud-billing-php.svg?style=flat-square)](https://packagist.org/packages/tigusigalpa/yandexcloud-billing-php)

PHP/Laravel клиентская библиотека для **Yandex Cloud Billing API** — сервиса управления биллинговыми аккаунтами, бюджетами и анализа расходов в Yandex Cloud.

## 📚 Документация

- [Документация Yandex Cloud Billing](https://yandex.cloud/ru/docs/billing/)
- [Руководство по быстрому старту](https://yandex.cloud/ru/docs/billing/quickstart/)
- [Справочник API](https://yandex.cloud/ru/docs/billing/api-ref/)
- [Руководство по OAuth токенам](https://yandex.cloud/ru/docs/iam/concepts/authorization/oauth-token)
- API Endpoint: `https://billing.api.cloud.yandex.net`

## ✨ Возможности

- ✅ Полная поддержка Yandex Cloud Billing API
- ✅ **Автоматическая генерация IAM токена из OAuth токена**
- ✅ Управление биллинговыми аккаунтами
- ✅ Создание и управление бюджетами с уведомлениями
- ✅ Привязка облаков к биллинговым аккаунтам
- ✅ Управление правами доступа
- ✅ OAuth и Service Account аутентификация
- ✅ **Кэширование IAM токенов** (файловая система, Redis)
- ✅ PHP 8.0+ со строгой типизацией
- ✅ Интеграция с Laravel 8-12 (service provider, facade, config)
- ✅ **Artisan команды** для управления через CLI
- ✅ Типизированные исключения для лучшей обработки ошибок
- ✅ Валидация данных перед отправкой в API
- ✅ Комплексное покрытие тестами

## 📦 Установка

```bash
composer require tigusigalpa/yandexcloud-billing-php
```

## ⚙️ Конфигурация (Laravel)

Опубликуйте файл конфигурации:

```bash
php artisan vendor:publish --tag=yandex-cloud-billing-config
```

Добавьте переменные окружения в ваш `.env`:

```env
# РЕКОМЕНДУЕТСЯ: Используйте OAuth токен (начинается с y0_, y1_, y2_, y3_)
# OAuth токены не истекают и автоматически конвертируются в IAM токены
YANDEX_OAUTH_TOKEN=y0_your-oauth-token

# АЛЬТЕРНАТИВА: Service Account (рекомендуется для production)
YANDEX_CLOUD_AUTH_TYPE=service_account
YANDEX_SERVICE_ACCOUNT_ID=your-service-account-id
YANDEX_SERVICE_ACCOUNT_KEY_ID=your-key-id
YANDEX_SERVICE_ACCOUNT_PRIVATE_KEY=your-private-key

# Настройки кэширования
YANDEX_CLOUD_CACHE_ENABLED=true
YANDEX_CLOUD_CACHE_DRIVER=file
YANDEX_CLOUD_CACHE_TTL=43200
```

## 🔐 Руководство по авторизации и подключению к API

### Шаг 1: Получение OAuth токена

**Документация:** [Руководство по OAuth токенам](https://yandex.cloud/ru/docs/iam/concepts/authorization/oauth-token)

**Получите токен через OAuth запрос:**

```
https://oauth.yandex.ru/authorize?response_type=token&client_id=1a6990aa636648e9b2ef855fa7bec2fb
```

1. Откройте URL выше в вашем браузере
2. Авторизуйте приложение
3. Скопируйте OAuth токен из URL ответа (формат: `y0_...`, `y1_...`, `y2_...`, `y3_...`)
4. Добавьте токен в `.env` (Laravel):
   ```env
   YANDEX_OAUTH_TOKEN=y0_your-oauth-token
   ```

**Или используйте Yandex CLI:**

```bash
yc iam create-token
```

⚠️ **Важно**: Никогда не записывайте токены прямо в код! Используйте переменные окружения или конфигурационные файлы.

### Шаг 2: Получение IAM токена (автоматически)

IAM токен генерируется автоматически из OAuth токена и кэшируется на 12 часов. Вам не нужно делать это вручную.

```php
use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;

// OAuth токен автоматически обменивается на IAM-токен
$client = new YandexCloudBillingClient('y0_your-oauth-token');
```

### Шаг 3: Service Account (для production)

**Документация:** [Service Account](https://yandex.cloud/ru/docs/iam/concepts/users/service-accounts)

Для production окружения рекомендуется использовать Service Account:

```php
use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;

$client = YandexCloudBillingClient::createWithServiceAccount(
    'service-account-id',
    'key-id',
    'private-key-content'
);
```

## 🚀 Быстрый старт

### Базовое использование (PHP)

```php
<?php

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;

try {
    // OAuth токен автоматически обменивается на IAM-токен
    $client = new YandexCloudBillingClient('y0_your-oauth-token');

    // Получение списка биллинговых аккаунтов
    $accounts = $client->billingAccount()->list();
    
    foreach ($accounts['billingAccounts'] as $account) {
        echo "Account: {$account['name']} (ID: {$account['id']})\n";
        echo "Balance: {$account['balance']}\n";
    }

    // Получение информации о конкретном аккаунте
    $account = $client->billingAccount()->get('billing-account-id');

    // Получение списка бюджетов
    $budgets = $client->budget()->list('billing-account-id');
    
} catch (AuthenticationException $e) {
    echo "Ошибка аутентификации: " . $e->getMessage();
} catch (YandexCloudBillingException $e) {
    echo "Ошибка API: " . $e->getMessage();
}
```

### Laravel Facade

```php
use Tigusigalpa\YandexCloudBilling\Laravel\Facades\YandexCloudBilling;

// Список биллинговых аккаунтов
$accounts = YandexCloudBilling::billingAccount()->list();

// Получить информацию об аккаунте
$account = YandexCloudBilling::billingAccount()->get('billing-account-id');

// Получить список привязанных облаков
$clouds = YandexCloudBilling::billingAccount()->listBoundClouds('billing-account-id');

// Создать бюджет
$budget = YandexCloudBilling::budget()->create([
    'billingAccountId' => 'billing-account-id',
    'name' => 'Месячный бюджет',
    'amount' => '10000.00',
    'resetPeriod' => 'MONTHLY',
]);

// Получить список бюджетов
$budgets = YandexCloudBilling::budget()->list('billing-account-id');
```

### Service Account (для production)

```php
<?php

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;

// Создание клиента с Service Account
$client = YandexCloudBillingClient::createWithServiceAccount(
    'service-account-id',
    'key-id',
    'private-key-content'
);

// Все операции работают аналогично OAuth
$accounts = $client->billingAccount()->list();
```

## 📖 Подробные примеры использования

### Работа с биллинговыми аккаунтами

**Документация:** [BillingAccount API](https://yandex.cloud/ru/docs/billing/api-ref/BillingAccount/)

```php
<?php

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;

$client = new YandexCloudBillingClient('y0_your-oauth-token');

// Получение списка всех биллинговых аккаунтов
$accounts = $client->billingAccount()->list();

foreach ($accounts['billingAccounts'] as $account) {
    echo "ID: {$account['id']}\n";
    echo "Name: {$account['name']}\n";
    echo "Status: {$account['status']}\n";
    echo "Balance: {$account['balance']}\n";
    echo "Currency: {$account['currency']}\n";
    echo "---\n";
}

// Получение информации о конкретном аккаунте
$accountId = 'your-billing-account-id';
$account = $client->billingAccount()->get($accountId);

// Получение списка привязанных облаков
$boundClouds = $client->billingAccount()->listBoundClouds($accountId);

foreach ($boundClouds['billableObjectBindings'] as $binding) {
    echo "Cloud ID: {$binding['effectiveTime']}\n";
}

// Привязка облака к биллинговому аккаунту
$result = $client->billingAccount()->bindBillableObject(
    $accountId, 
    'cloud-id',
    'cloud' // тип объекта: 'cloud' или 'folder'
);

// Получение списка привязок доступа
$accessBindings = $client->billingAccount()->listAccessBindings($accountId);
```

### Laravel Facade - Биллинговые аккаунты

```php
use Tigusigalpa\YandexCloudBilling\Laravel\Facades\YandexCloudBilling;

// Получить все аккаунты
$accounts = YandexCloudBilling::billingAccount()->list();

// Получить конкретный аккаунт
$account = YandexCloudBilling::billingAccount()->get('billing-account-id');

// Список привязанных облаков
$clouds = YandexCloudBilling::billingAccount()->listBoundClouds('billing-account-id');

// Привязать облако
$result = YandexCloudBilling::billingAccount()->bindBillableObject(
    'billing-account-id',
    'cloud-id'
);
```

### Управление бюджетами

**Документация:** [Budget API](https://yandex.cloud/ru/docs/billing/api-ref/Budget/)

```php
<?php

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Validators\BudgetValidator;

$client = new YandexCloudBillingClient('y0_your-oauth-token');

// Создание нового бюджета с уведомлениями
$budgetData = [
    'billingAccountId' => 'your-billing-account-id',
    'name' => 'Месячный бюджет на инфраструктуру',
    'amount' => '10000.00',
    'resetPeriod' => 'MONTHLY', // MONTHLY, QUARTER, ANNUALLY
    'startDate' => '2024-01-01',
    'endDate' => '2024-12-31',
    'thresholdRules' => [
        [
            'thresholdValue' => 50,
            'thresholdType' => 'PERCENT', // PERCENT или AMOUNT
            'sendNotificationsToIds' => ['user-account-id']
        ],
        [
            'thresholdValue' => 80,
            'thresholdType' => 'PERCENT',
            'sendNotificationsToIds' => ['user-account-id']
        ],
        [
            'thresholdValue' => 100,
            'thresholdType' => 'PERCENT',
            'sendNotificationsToIds' => ['user-account-id']
        ]
    ]
];

// Валидация данных перед отправкой
BudgetValidator::validateCreateData($budgetData);

// Создание бюджета
$budget = $client->budget()->create($budgetData);
echo "Создан бюджет: {$budget['id']}\n";

// Получение списка бюджетов
$budgets = $client->budget()->list('billing-account-id');

foreach ($budgets['budgets'] as $budget) {
    echo "Budget: {$budget['name']}\n";
    echo "Amount: {$budget['amount']}\n";
    echo "Status: {$budget['status']}\n";
}

// Получение конкретного бюджета
$budget = $client->budget()->get('budget-id');

// Обновление бюджета
$updateData = [
    'amount' => '15000.00',
    'name' => 'Обновленный месячный бюджет'
];
$updatedBudget = $client->budget()->update('budget-id', $updateData);

// Удаление бюджета
$client->budget()->delete('budget-id');
```

### Laravel Facade - Бюджеты

```php
use Tigusigalpa\YandexCloudBilling\Laravel\Facades\YandexCloudBilling;

// Создать бюджет
$budget = YandexCloudBilling::budget()->create([
    'billingAccountId' => 'billing-account-id',
    'name' => 'Production Budget',
    'amount' => '50000.00',
    'resetPeriod' => 'MONTHLY',
    'thresholdRules' => [
        [
            'thresholdValue' => 80,
            'thresholdType' => 'PERCENT',
            'sendNotificationsToIds' => ['user-id']
        ]
    ]
]);

// Получить список бюджетов
$budgets = YandexCloudBilling::budget()->list('billing-account-id');

// Получить конкретный бюджет
$budget = YandexCloudBilling::budget()->get('budget-id');

// Обновить бюджет
$updated = YandexCloudBilling::budget()->update('budget-id', [
    'amount' => '60000.00'
]);

// Удалить бюджет
YandexCloudBilling::budget()->delete('budget-id');
```

### Обработка ошибок

Библиотека предоставляет специфические исключения для различных типов ошибок:

```php
<?php

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;
use Tigusigalpa\YandexCloudBilling\Exceptions\ValidationException;

$client = new YandexCloudBillingClient('y0_your-oauth-token');

try {
    // Попытка получить список аккаунтов
    $accounts = $client->billingAccount()->list();
    
    // Попытка создать бюджет
    $budget = $client->budget()->create([
        'billingAccountId' => 'billing-account-id',
        'name' => 'Test Budget',
        'amount' => '10000.00',
        'resetPeriod' => 'MONTHLY',
    ]);
    
} catch (AuthenticationException $e) {
    // Ошибка аутентификации (неверный токен, истек срок действия)
    echo "Ошибка аутентификации: " . $e->getMessage();
    echo "Код ошибки: " . $e->getCode();
    
} catch (ValidationException $e) {
    // Ошибка валидации данных
    echo "Ошибка валидации: " . $e->getMessage();
    echo "Проверьте корректность переданных данных";
    
} catch (YandexCloudBillingException $e) {
    // Общая ошибка API
    echo "Ошибка API: " . $e->getMessage();
    echo "Код ошибки: " . $e->getCode();
    
    // Получить детали ошибки
    if (method_exists($e, 'getResponse')) {
        $response = $e->getResponse();
        echo "Детали: " . json_encode($response);
    }
}
```

### Laravel Artisan команды

Пакет предоставляет удобные Artisan команды для управления биллингом через CLI:

#### Тестирование подключения

```bash
# Проверить подключение к API
php artisan billing:test
```

#### Список биллинговых аккаунтов

```bash
# Получить список всех биллинговых аккаунтов
php artisan billing:list-accounts

# Пример вывода:
# Billing Accounts:
# ID: billing-account-id
# Name: My Billing Account
# Status: ACTIVE
# Balance: 1000.50
# Currency: RUB
```

#### Создание бюджета

```bash
# Создать новый бюджет
php artisan billing:create-budget

# Команда запросит:
# - Billing Account ID
# - Название бюджета
# - Сумму бюджета
# - Период сброса (MONTHLY/QUARTER/ANNUALLY)
# - Пороговые значения для уведомлений
```

#### Примеры использования команд

```bash
# Тест подключения
php artisan billing:test

# Список аккаунтов
php artisan billing:list-accounts

# Создание бюджета интерактивно
php artisan billing:create-budget
```

## 📁 Примеры кода

В папке `examples/` вы найдете дополнительные примеры:

- **`basic_usage.php`** - Базовое использование API, получение списка аккаунтов
- **`budget_management.php`** - Полный цикл управления бюджетами (создание, обновление, удаление)
- **`service_account_usage.php`** - Использование Service Account для аутентификации

## 📚 Справочник API

### YandexCloudBillingClient

Основной класс для работы с Yandex Cloud Billing API.

#### Конструктор

```php
// OAuth аутентификация
$client = new YandexCloudBillingClient(string $oauthToken);

// Service Account аутентификация
$client = YandexCloudBillingClient::createWithServiceAccount(
    string $serviceAccountId,
    string $keyId,
    string $privateKey
);
```

#### Методы

- **`billingAccount(): BillingAccountResource`** - Возвращает ресурс для работы с биллинговыми аккаунтами
- **`budget(): BudgetResource`** - Возвращает ресурс для работы с бюджетами
- **`getHttpClient(): ClientInterface`** - Получить HTTP клиент
- **`getAuthManager()`** - Получить менеджер аутентификации

### BillingAccountResource

Ресурс для управления биллинговыми аккаунтами.

**Документация:** [BillingAccount API](https://yandex.cloud/ru/docs/billing/api-ref/BillingAccount/)

#### Методы

- **`list(): array`**
  - Получить список всех биллинговых аккаунтов
  - Возвращает: массив с ключом `billingAccounts`

- **`get(string $billingAccountId): array`**
  - Получить информацию о конкретном биллинговом аккаунте
  - Параметры:
    - `$billingAccountId` - ID биллингового аккаунта
  - Возвращает: массив с данными аккаунта

- **`listBoundClouds(string $billingAccountId): array`**
  - Получить список облаков, привязанных к биллинговому аккаунту
  - Параметры:
    - `$billingAccountId` - ID биллингового аккаунта
  - Возвращает: массив с ключом `billableObjectBindings`

- **`bindBillableObject(string $billingAccountId, string $billableObjectId, string $billableObjectType = 'cloud'): array`**
  - Привязать облако или папку к биллинговому аккаунту
  - Параметры:
    - `$billingAccountId` - ID биллингового аккаунта
    - `$billableObjectId` - ID облака или папки
    - `$billableObjectType` - Тип объекта: `'cloud'` или `'folder'`
  - Возвращает: результат операции

- **`listAccessBindings(string $billingAccountId): array`**
  - Получить список привязок доступа к биллинговому аккаунту
  - Параметры:
    - `$billingAccountId` - ID биллингового аккаунта
  - Возвращает: массив привязок доступа

### BudgetResource

Ресурс для управления бюджетами.

**Документация:** [Budget API](https://yandex.cloud/ru/docs/billing/api-ref/Budget/)

#### Методы

- **`get(string $budgetId): array`**
  - Получить информацию о конкретном бюджете
  - Параметры:
    - `$budgetId` - ID бюджета
  - Возвращает: массив с данными бюджета

- **`list(string $billingAccountId): array`**
  - Получить список бюджетов для биллингового аккаунта
  - Параметры:
    - `$billingAccountId` - ID биллингового аккаунта
  - Возвращает: массив с ключом `budgets`

- **`create(array $budgetData): array`**
  - Создать новый бюджет
  - Параметры:
    - `$budgetData` - Массив с данными бюджета:
      - `billingAccountId` (string, обязательно) - ID биллингового аккаунта
      - `name` (string, обязательно) - Название бюджета
      - `amount` (string, обязательно) - Сумма бюджета
      - `resetPeriod` (string) - Период сброса: `MONTHLY`, `QUARTER`, `ANNUALLY`
      - `startDate` (string) - Дата начала в формате `YYYY-MM-DD`
      - `endDate` (string) - Дата окончания в формате `YYYY-MM-DD`
      - `thresholdRules` (array) - Правила уведомлений
  - Возвращает: массив с данными созданного бюджета

- **`update(string $budgetId, array $budgetData, ?string $updateMask = null): array`**
  - Обновить существующий бюджет
  - Параметры:
    - `$budgetId` - ID бюджета
    - `$budgetData` - Массив с обновляемыми данными
    - `$updateMask` - Маска обновления (опционально)
  - Возвращает: массив с обновленными данными бюджета

- **`delete(string $budgetId): void`**
  - Удалить бюджет
  - Параметры:
    - `$budgetId` - ID бюджета

### Исключения

- **`YandexCloudBillingException`** - Базовое исключение для всех ошибок API
- **`AuthenticationException`** - Ошибка аутентификации (неверный токен, истек срок)
- **`ValidationException`** - Ошибка валидации данных

## 🔗 Дополнительная документация

- [Официальная документация Yandex Cloud Billing](https://yandex.cloud/ru/docs/billing/)
- [Billing API Reference](https://yandex.cloud/ru/docs/billing/api-ref/)
- [Руководство по аутентификации](AUTHENTICATION.md)

## 🧪 Тестирование

### PHPUnit тесты

```bash
# Запустить все тесты
composer test

# Запустить тесты с покрытием кода
composer test-coverage
```

### Анализ кода

```bash
# PHPStan статический анализ
composer phpstan

# Проверка стиля кода (PSR-12)
composer cs-check

# Автоматическое исправление стиля кода
composer cs-fix
```

## 📝 Требования

- **PHP** 8.0 или выше
- **Laravel** 8.x - 12.x (опционально, для интеграции с Laravel)
- **Guzzle HTTP client** 7.x или выше
- **firebase/php-jwt** 6.x или выше (для Service Account)
- Расширение **ext-json**

## 🤝 Участие в разработке

Приветствуются любые вклады! Пожалуйста, не стесняйтесь отправлять Pull Request.

1. Сделайте форк репозитория
2. Создайте ветку для новой функции (`git checkout -b feature/amazing-feature`)
3. Зафиксируйте изменения (`git commit -m 'Add some amazing feature'`)
4. Отправьте в ветку (`git push origin feature/amazing-feature`)
5. Откройте Pull Request

## 📄 Лицензия

Этот пакет является программным обеспечением с открытым исходным кодом, лицензированным по [лицензии MIT](LICENSE).

## 👤 Автор

**Igor Sazonov**

- GitHub: [@tigusigalpa](https://github.com/tigusigalpa)
- Email: sovletig@gmail.com

## 🔗 Ссылки

- [Packagist](https://packagist.org/packages/tigusigalpa/yandexcloud-billing-php)
- [GitHub Repository](https://github.com/tigusigalpa/yandexcloud-billing-php)
- [Issue Tracker](https://github.com/tigusigalpa/yandexcloud-billing-php/issues)
- [Документация Yandex Cloud Billing](https://yandex.cloud/ru/docs/billing/)

## ⭐ Поддержите проект

Если этот пакет оказался полезным для вас, поставьте звезду ⭐ на [GitHub](https://github.com/tigusigalpa/yandexcloud-billing-php)!
