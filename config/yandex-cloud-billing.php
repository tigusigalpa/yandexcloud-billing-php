<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Yandex Cloud Billing Authentication
    |--------------------------------------------------------------------------
    |
    | Выберите тип аутентификации: 'oauth' или 'service_account'
    | Для production рекомендуется использовать Service Account
    |
    */

    'auth_type' => env('YANDEX_CLOUD_AUTH_TYPE', 'oauth'),

    /*
    |--------------------------------------------------------------------------
    | OAuth Token Authentication
    |--------------------------------------------------------------------------
    |
    | OAuth токен для аутентификации через пользователя
    | Получите токен: yc iam create-token
    |
    */

    'oauth_token' => env('YANDEX_OAUTH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Service Account Authentication
    |--------------------------------------------------------------------------
    |
    | Настройки для аутентификации через Service Account
    | Рекомендуется для production окружения
    |
    */

    'service_account' => [
        'id' => env('YANDEX_SERVICE_ACCOUNT_ID'),
        'key_id' => env('YANDEX_SERVICE_ACCOUNT_KEY_ID'),
        'private_key' => env('YANDEX_SERVICE_ACCOUNT_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки HTTP клиента для запросов к API
    |
    */

    'http' => [
        'timeout' => env('YANDEX_CLOUD_HTTP_TIMEOUT', 30),
        'connect_timeout' => env('YANDEX_CLOUD_HTTP_CONNECT_TIMEOUT', 10),
        'retry_attempts' => env('YANDEX_CLOUD_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('YANDEX_CLOUD_RETRY_DELAY', 1000), // миллисекунды
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки кэширования IAM токенов
    |
    */

    'cache' => [
        'enabled' => env('YANDEX_CLOUD_CACHE_ENABLED', true),
        'driver' => env('YANDEX_CLOUD_CACHE_DRIVER', 'file'),
        'prefix' => env('YANDEX_CLOUD_CACHE_PREFIX', 'yandex_cloud_'),
        'ttl' => env('YANDEX_CLOUD_CACHE_TTL', 43200), // 12 часов в секундах
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки логирования запросов к API
    |
    */

    'logging' => [
        'enabled' => env('YANDEX_CLOUD_LOGGING_ENABLED', false),
        'channel' => env('YANDEX_CLOUD_LOG_CHANNEL', 'default'),
        'level' => env('YANDEX_CLOUD_LOG_LEVEL', 'info'),
        'log_requests' => env('YANDEX_CLOUD_LOG_REQUESTS', true),
        'log_responses' => env('YANDEX_CLOUD_LOG_RESPONSES', false),
    ],
];
