<?php
declare(strict_types=1);

/** Escape a value for HTML output (XSS defence). */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Absolute path prefix of the public folder, e.g. /BikeRental/public */
function base_path(): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    return rtrim($dir, '/');
}

/** Build a URL to a route: url('bikes', ['status' => 'available']) */
function url(string $route = 'dashboard', array $params = []): string
{
    $query = http_build_query(['route' => $route] + $params);
    return base_path() . '/index.php?' . $query;
}

function asset(string $path): string
{
    return base_path() . '/assets/' . ltrim($path, '/');
}

function redirect(string $route, array $params = []): never
{
    header('Location: ' . url($route, $params));
    exit;
}

/** Hidden input carrying the CSRF token. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(\App\Support\Csrf::token()) . '">';
}

/** Old form input helper: old($old, 'field', 'default') */
function old(array $old, string $key, mixed $default = ''): string
{
    return e($old[$key] ?? $default);
}

function money(mixed $amount): string
{
    return number_format((float)$amount, 0, '.', ',') . ' RWF';
}

function status_badge(string $status): string
{
    return '<span class="badge badge-' . e($status) . '">' . e(ucfirst($status)) . '</span>';
}

/** Inline error message for a field. */
function field_error(array $errors, string $key): string
{
    return isset($errors[$key]) ? '<span class="error-message">' . e($errors[$key]) . '</span>' : '';
}

/** CSS class for an input that has an error. */
function err_class(array $errors, string $key): string
{
    return isset($errors[$key]) ? ' input-error' : '';
}

/** Small POST form with CSRF used for delete / return / cancel buttons. */
function post_button(string $route, int $id, string $label, string $class = 'btn btn-small', ?string $confirm = null): string
{
    return '<form method="post" action="' . e(url($route)) . '" class="inline-form"'
        . ($confirm ? ' data-confirm="' . e($confirm) . '"' : '') . '>'
        . csrf_field() . '<input type="hidden" name="id" value="' . $id . '">'
        . '<button type="submit" class="' . e($class) . '">' . e($label) . '</button></form>';
}
