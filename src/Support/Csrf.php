<?php
declare(strict_types=1);

namespace App\Support;

final class Csrf
{
    public static function token(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    }

    /** Every state-changing request must call this. */
    public static function verify(): void
    {
        $sent = (string)($_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if ($sent === '' || !hash_equals(self::token(), $sent)) {
            http_response_code(403);
            View::render('errors/403', [], 'layout/layout');
            exit;
        }
    }
}
