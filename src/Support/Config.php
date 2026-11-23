<?php
declare(strict_types=1);

namespace App\Support;

final class Config
{
    private static ?array $data = null;

    public static function get(string $key): mixed
    {
        self::$data ??= require __DIR__ . '/../../config/config.php';
        return self::$data[$key] ?? null;
    }
}
