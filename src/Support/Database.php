<?php
declare(strict_types=1);

namespace App\Support;

use PDO;
use PDOException;

/** Single shared PDO connection (prepared statements only, exceptions on error). */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $c = Config::get('db');
            $dsn = $c['dsn'] ?: "mysql:host={$c['host']};dbname={$c['name']};charset={$c['charset']}";
            try {
                self::$pdo = new PDO($dsn, $c['dsn'] ? null : $c['user'], $c['dsn'] ? null : $c['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                if (str_starts_with($dsn, 'sqlite')) {
                    self::$pdo->exec('PRAGMA foreign_keys = ON');
                }
            } catch (PDOException $e) {
                // Never leak credentials / host details to the browser.
                ErrorHandler::log($e);
                throw new \RuntimeException('Database connection failed.', 0, $e);
            }
        }
        return self::$pdo;
    }
}
