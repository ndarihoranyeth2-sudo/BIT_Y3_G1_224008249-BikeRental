<?php
declare(strict_types=1);

namespace App\Support;

use App\Models\User;

final class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'admin';
    }

    /** Authentication: must be logged in. */
    public static function require(): void
    {
        if (!self::check()) {
            Flash::error('Please log in to continue.');
            redirect('login');
        }
    }

    /** Authorisation: must hold the role. */
    public static function requireAdmin(): void
    {
        self::require();
        if (!self::isAdmin()) {
            http_response_code(403);
            View::render('errors/403', [], 'layout/layout');
            exit;
        }
    }

    public static function attempt(string $username, string $password): bool
    {
        $row = (new User())->findByUsername($username);
        // Always run password_verify to keep timing similar for unknown users.
        $hash = $row['password_hash'] ?? password_hash('dummy', PASSWORD_DEFAULT);
        if ($row && password_verify($password, $hash)) {
            session_regenerate_id(true);   // prevents session fixation
            $_SESSION['user'] = [
                'user_id'  => (int)$row['user_id'],
                'username' => $row['username'],
                'role'     => $row['role'],
            ];
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
