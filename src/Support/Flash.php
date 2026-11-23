<?php
declare(strict_types=1);

namespace App\Support;

/** One-time messages stored in the session (Post-Redirect-Get). */
final class Flash
{
    public static function add(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function success(string $m): void { self::add('success', $m); }
    public static function error(string $m): void   { self::add('error', $m); }

    /** Read and remove. */
    public static function pull(): array
    {
        $items = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $items;
    }
}
