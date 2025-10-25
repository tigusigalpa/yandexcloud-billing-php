# Аутентификация в Yandex Cloud Billing API

Для работы с Yandex Cloud Billing API используется система аутентификации через **IAM-токены**, которые автоматически получаются из OAuth токенов.

## Как работает аутентификация

1. Вы предоставляете **OAuth токен** при создании клиента
2. Клиент автоматически обменивает OAuth токен на **IAM-токен**
3. IAM-токен используется для всех API запросов
4. IAM-токен автоматически обновляется при истечении срока действия

## Получение OAuth токена

### Способ 1: Через Yandex Cloud CLI (рекомендуется)

1. Установите [Yandex Cloud CLI](https://cloud.yandex.ru/docs/cli/quickstart)
2. Выполните аутентификацию:
   ```bash
   yc init
   ```
3. Получите OAuth токен:
   ```bash
   yc iam create-token
   ```

### Способ 2: Через веб-интерфейс

1. Перейдите на страницу [OAuth токенов](https://oauth.yandex.ru/authorize?response_type=token&client_id=1a6990aa636648e9b2ef855fa7bec2fb)
2. Разрешите доступ к Yandex Cloud API
3. Скопируйте полученный токен

### Способ 3: Через API

Отправьте POST запрос на `https://oauth.yandex.ru/token` с параметрами:
- `grant_type=authorization_code`
- `code=<authorization_code>`
- `client_id=<client_id>`
- `client_secret=<client_secret>`

## Использование в PHP

### Базовое использование

```php
use Tigusigalpa\YandexCloudBilling\YandexCloudBillingClient;

// OAuth токен автоматически обменивается на IAM-токен
$oauthToken = 'your-oauth-token-here';
$client = new YandexCloudBillingClient($oauthToken);

// Все запросы используют IAM-токен автоматически
$accounts = $client->billingAccount()->list();
```

### Использование переменных окружения (рекомендуется)

```php
// Установите переменную окружения
// export YANDEX_OAUTH_TOKEN="your-oauth-token-here"

$oauthToken = $_ENV['YANDEX_OAUTH_TOKEN'];
$client = new YandexCloudBillingClient($oauthToken);
```

### Использование .env файла

```php
// В файле .env
// YANDEX_OAUTH_TOKEN=your-oauth-token-here

// В PHP коде (с использованием vlucas/phpdotenv)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$oauthToken = $_ENV['YANDEX_OAUTH_TOKEN'];
$client = new YandexCloudBillingClient($oauthToken);
```

## Автоматическое управление IAM-токенами

Библиотека автоматически:

- **Получает IAM-токен** из OAuth токена при первом запросе
- **Кэширует IAM-токен** для повторного использования
- **Проверяет срок действия** перед каждым запросом
- **Обновляет IAM-токен** автоматически при истечении (каждые ~12 часов)

Вам не нужно вручную управлять IAM-токенами!

## Безопасность

### ⚠️ Важные предупреждения

1. **Никогда не храните токены в коде**: Не записывайте токены прямо в исходный код
2. **Используйте переменные окружения**: Храните токены в переменных окружения или конфигурационных файлах
3. **Ограничьте доступ**: Убедитесь, что файлы с токенами недоступны через веб-сервер
4. **Регулярно обновляйте**: OAuth токены могут иметь ограниченный срок действия

### Рекомендуемые способы хранения

1. **Переменные окружения**:
   ```bash
   export YANDEX_OAUTH_TOKEN="your-token"
   ```

2. **Конфигурационный файл** (вне веб-директории):
   ```php
   // config/yandex.php
   return [
       'oauth_token' => 'your-token-here'
   ];
   ```

3. **Файл .env** (добавьте в .gitignore):
   ```
   YANDEX_OAUTH_TOKEN=your-token-here
   ```

## Проверка аутентификации

Вы можете проверить валидность токена:

```php
try {
    $client = new YandexCloudBillingClient($oauthToken);
    $accounts = $client->billingAccount()->list();
    echo "Аутентификация успешна\n";
} catch (AuthenticationException $e) {
    echo "Ошибка аутентификации: " . $e->getMessage() . "\n";
    echo "Получите новый OAuth токен: yc iam create-token\n";
}
```

## Технические детали

### Срок действия токенов

- **OAuth токены**: Обычно долгосрочные (до отзыва)
- **IAM-токены**: 12 часов (автоматически обновляются)

### API endpoints

Библиотека использует следующие endpoints:
- **IAM API**: `https://iam.api.cloud.yandex.net/iam/v1/tokens` - для получения IAM-токенов
- **Billing API**: `https://billing.api.cloud.yandex.net/billing/v1/` - для работы с биллингом

### Заголовки аутентификации

Все запросы к Billing API содержат заголовок:
```
Authorization: Bearer <iam-token>
```

## Service Account (для production)

Для production-окружения рекомендуется использовать Service Account:

1. Создайте Service Account в консоли Yandex Cloud
2. Назначьте необходимые роли (`billing.accounts.viewer`, `billing.budgets.editor`)
3. Создайте авторизованный ключ
4. Используйте JWT токены вместо OAuth

Пример работы с Service Account будет добавлен в следующих версиях библиотеки.

## Устранение проблем

### Ошибка "401 Unauthorized"

1. Проверьте, что OAuth токен не является placeholder'ом
2. Получите новый OAuth токен: `yc iam create-token`
3. Убедитесь, что у вас есть права доступа к Billing API

### Ошибка "403 Forbidden"

1. Проверьте права доступа к биллинговому аккаунту
2. Убедитесь, что у пользователя есть роль `billing.accounts.viewer` или выше

### Проблемы с сетью

1. Проверьте доступность `iam.api.cloud.yandex.net`
2. Проверьте доступность `billing.api.cloud.yandex.net`
3. Убедитесь, что нет блокировки HTTPS трафика