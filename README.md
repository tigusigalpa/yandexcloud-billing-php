# Yandex Cloud Billing PHP SDK

![Yandex Cloud Billing PHP SDK](https://i.ibb.co/RkMtJq1t/c-J5-WHu-Ugcy-LQJBuz-WM97y-T.png)

PHP SDK для работы с Yandex Cloud Billing API.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tigusigalpa/yandexcloud-billing-php.svg?style=flat-square)](https://packagist.org/packages/tigusigalpa/yandexcloud-billing-php)
[![Total Downloads](https://img.shields.io/packagist/dt/tigusigalpa/yandexcloud-billing-php.svg?style=flat-square)](https://packagist.org/packages/tigusigalpa/yandexcloud-billing-php)
[![License](https://img.shields.io/packagist/l/tigusigalpa/yandexcloud-billing-php.svg?style=flat-square)](https://packagist.org/packages/tigusigalpa/yandexcloud-billing-php)

## Описание

Этот пакет предоставляет удобный интерфейс для работы с Yandex Cloud Billing API, позволяя получать информацию о
биллинговых аккаунтах и бюджетах.

## Возможности

- Получение информации о биллинговых аккаунтах
- Работа с бюджетами
- Анализ использования ресурсов и стоимости
- Поддержка OAuth и Service Account аутентификации
- Кэширование IAM токенов (файловая система, Redis)
- Laravel интеграция с Service Provider и Facade
- Artisan команды для управления
- Простой и интуитивный API
- Полная поддержка PSR-4 автозагрузки
- Обработка ошибок и исключений

## Требования

- PHP 8.0 или выше
- Расширение JSON
- Guzzle HTTP клиент

## Установка

Установите пакет через Composer:

```bash
composer require tigusigalpa/yandexcloud-billing-php
```

## Аутентификация

Библиотека использует систему аутентификации через **IAM-токены**, которые автоматически получаются из OAuth токенов.

### Получение OAuth токена

1. **Через Yandex Cloud CLI** (рекомендуется):
   ```bash
   yc iam create-token
   ```

2. **Через веб-интерфейс**: Перейдите
   на [страницу OAuth токенов](https://oauth.yandex.ru/authorize?response_type=token&client_id=1a6990aa636648e9b2ef855fa7bec2fb)

3. **Через переменную окружения**:
   ```bash
   export YANDEX_OAUTH_TOKEN="your-oauth-token-here"
   ```

⚠️ **Важно**: Никогда не записывайте токены прямо в код! Используйте переменные окружения или конфигурационные файлы.

Подробная информация: [AUTHENTICATION.md](AUTHENTICATION.md)

## Быстрый старт

### OAuth аутентификация

```php
<?php

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;

try {
    // OAuth токен автоматически обменивается на IAM-токен
    $oauthToken = getenv('YANDEX_OAUTH_TOKEN'); // Из переменной окружения
    
    // Создание клиента (с автоматическим получением IAM-токена)
    $client = new YandexCloudBillingClient($oauthToken);

    // Получение списка биллинговых аккаунтов
    $accounts = $client->billingAccount()->list();

    // Получение информации о конкретном аккаунте
    $account = $client->billingAccount()->get('billing-account-id');

    // Получение бюджета
    $budget = $client->budget()->get('budget-id');
    
} catch (AuthenticationException $e) {
    echo "Ошибка аутентификации: " . $e->getMessage();
    echo "Получите новый токен: yc iam create-token";
} catch (YandexCloudBillingException $e) {
    echo "Ошибка API: " . $e->getMessage();
}
```

### Service Account аутентификация (рекомендуется для production)

```php
<?php

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Cache\FileCache;

// Создание клиента с Service Account
$client = YandexCloudBillingClient::createWithServiceAccount(
    'service-account-id',
    'key-id',
    'private-key-content'
);

// Все остальные операции аналогичны OAuth
$accounts = $client->billingAccount()->list();
```

## Подробные примеры использования

### Работа с биллинговыми аккаунтами

```php
<?php

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;

$client = new YandexCloudBillingClient('your-oauth-token');

// Получение списка всех биллинговых аккаунтов
$accounts = $client->billingAccount()->list();

// Получение информации о конкретном аккаунте
$accountId = 'your-billing-account-id';
$account = $client->billingAccount()->get($accountId);

// Получение списка привязанных облаков
$boundClouds = $client->billingAccount()->listBoundClouds($accountId);

// Привязка облака к биллинговому аккаунту
$result = $client->billingAccount()->bindBillableObject($accountId, 'cloud-id');

// Получение списка привязок доступа
$accessBindings = $client->billingAccount()->listAccessBindings($accountId);
```

### Управление бюджетами

```php
<?php

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Validators\BudgetValidator;

$client = new YandexCloudBillingClient('your-oauth-token');

// Создание нового бюджета
$budgetData = [
    'billingAccountId' => 'your-billing-account-id',
    'name' => 'Месячный бюджет',
    'amount' => '10000.00',
    'resetPeriod' => 'MONTHLY',
    'thresholdRules' => [
        [
            'thresholdValue' => 80,
            'thresholdType' => 'PERCENT',
            'sendNotificationsToIds' => ['user-id']
        ]
    ]
];

// Валидация данных
BudgetValidator::validateCreateData($budgetData);

// Создание бюджета
$budget = $client->budget()->create($budgetData);

// Получение списка бюджетов
$budgets = $client->budget()->list('billing-account-id');

// Обновление бюджета
$updateData = ['amount' => '15000.00'];
$updatedBudget = $client->budget()->update('budget-id', $updateData);

// Удаление бюджета
$client->budget()->delete('budget-id');
```

### Обработка ошибок

```php
<?php

use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;
use Tigusigalpa\YandexCloudBilling\Exceptions\YandexCloudBillingException;
use Tigusigalpa\YandexCloudBilling\Exceptions\AuthenticationException;
use Tigusigalpa\YandexCloudBilling\Exceptions\ValidationException;

$client = new YandexCloudBillingClient('your-oauth-token');

try {
    $accounts = $client->billingAccount()->list();
} catch (AuthenticationException $e) {
    echo "Ошибка аутентификации: " . $e->getMessage();
} catch (ValidationException $e) {
    echo "Ошибка валидации: " . $e->getMessage();
} catch (YandexCloudBillingException $e) {
    echo "Общая ошибка API: " . $e->getMessage();
}
```

## Примеры

В папке `examples/` вы найдете дополнительные примеры:

- `basic_usage.php` - Базовое использование API
- `budget_management.php` - Управление бюджетами

## API Reference

### YandexCloudBillingClient

Основной класс для работы с API.

#### Методы

- `billingAccount()` - Возвращает ресурс для работы с биллинговыми аккаунтами
- `budget()` - Возвращает ресурс для работы с бюджетами

### BillingAccountResource

#### Методы

- `list()` - Получить список биллинговых аккаунтов
- `get(string $billingAccountId)` - Получить информацию о биллинговом аккаунте
- `listBoundClouds(string $billingAccountId)` - Получить список привязанных облаков
- `bindBillableObject(string $billingAccountId, string $billableObjectId, string $billableObjectType = 'cloud')` -
  Привязать объект к биллинговому аккаунту
- `listAccessBindings(string $billingAccountId)` - Получить список привязок доступа

### BudgetResource

#### Методы

- `get(string $budgetId)` - Получить информацию о бюджете
- `list(string $billingAccountId)` - Получить список бюджетов
- `create(array $budgetData)` - Создать бюджет
- `update(string $budgetId, array $budgetData, ?string $updateMask = null)` - Обновить бюджет
- `delete(string $budgetId)` - Удалить бюджет

## Документация

Подробная документация доступна
в [официальной документации Yandex Cloud](https://yandex.cloud/ru/docs/billing/api-ref/).

## Тестирование

```bash
composer test
```

## Анализ кода

```bash
composer phpstan
```

## Проверка стиля кода

```bash
composer cs-check
```

## Исправление стиля кода

```bash
composer cs-fix
```

## Лицензия

MIT License. Подробности в файле [LICENSE](LICENSE).

## Автор

- **tigusigalpa** - [GitHub](https://github.com/tigusigalpa)
- Email: sovletig@gmail.com

## Поддержка

Если у вас есть вопросы или предложения, создайте issue
в [GitHub репозитории](https://github.com/tigusigalpa/yandexcloud-billing-php/issues).
