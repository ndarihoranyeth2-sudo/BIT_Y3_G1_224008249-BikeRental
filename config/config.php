<?php
declare(strict_types=1);

/**
 * Application configuration.
 * Values can be overridden with environment variables so that secrets
 * are not hard-coded in production.
 */
return [
    'db' => [
        'host'    => getenv('DB_HOST') ?: 'localhost',
        'name'    => getenv('DB_NAME') ?: 'bike_rental',
        'user'    => getenv('DB_USER') ?: 'root',
        'pass'    => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',   // XAMPP default: empty
        'charset' => 'utf8mb4',
        'dsn'     => getenv('DB_DSN') ?: null,   // optional full DSN override (used by tests)
    ],
    'timezone' => getenv('APP_TZ') ?: 'Africa/Kigali',   // rental times use this zone
    // "development" shows nothing sensitive either, but logs details privately.
    'env'      => getenv('APP_ENV') ?: 'development',
    'log_file' => __DIR__ . '/../storage-error.log',
];
