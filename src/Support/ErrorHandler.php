<?php
declare(strict_types=1);

namespace App\Support;

use Throwable;

/** Generic public messages, detailed private log. */
final class ErrorHandler
{
    public static function log(Throwable $e): void
    {
        $line = sprintf("[%s] %s: %s in %s:%d\n", date('c'), $e::class, $e->getMessage(), $e->getFile(), $e->getLine());
        @file_put_contents((string)Config::get('log_file'), $line, FILE_APPEND | LOCK_EX);
    }

    public static function register(): void
    {
        ini_set('display_errors', '0');
        set_exception_handler(function (Throwable $e): void {
            self::log($e);
            http_response_code(500);
            $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
                || (($_GET['route'] ?? '') === 'api-bikes');
            if ($wantsJson) {
                header('Content-Type: application/json');
                echo json_encode(['message' => 'Something went wrong. Please try again later.']);
                return;
            }
            View::render('errors/500', [], 'layout/layout');
        });
    }
}
