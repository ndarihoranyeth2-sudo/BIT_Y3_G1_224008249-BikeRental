<?php
declare(strict_types=1);

// PSR-4 style autoloader: App\Models\Bike -> src/Models/Bike.php (no Composer needed)
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require __DIR__ . '/Support/helpers.php';

date_default_timezone_set((string)App\Support\Config::get('timezone'));
App\Support\ErrorHandler::register();

// Hardened session cookie
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
