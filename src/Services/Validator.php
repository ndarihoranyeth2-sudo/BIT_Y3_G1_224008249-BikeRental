<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Bike;
use App\Models\Customer;
use App\Models\Rental;
use App\Models\User;

/**
 * Server-side validation (authoritative). Each method takes a plain input array
 * and returns a field-keyed error array. No output, no redirects.
 */
final class Validator
{
    public static function login(array $in): array
    {
        $e = [];
        if (trim((string)($in['username'] ?? '')) === '') $e['username'] = 'Username is required.';
        if ((string)($in['password'] ?? '') === '')       $e['password'] = 'Password is required.';
        return $e;
    }

    public static function user(array $in, int $excludeId = 0, bool $passwordRequired = true, bool $roleEditable = true): array
    {
        $e = [];
        $u = trim((string)($in['username'] ?? ''));
        if ($u === '') {
            $e['username'] = 'Username is required.';
        } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $u)) {
            $e['username'] = '3-30 characters: letters, digits and underscore only.';
        } elseif ((new User())->exists('username', $u, $excludeId)) {
            $e['username'] = 'This username is already taken.';
        }

        $m = trim((string)($in['email'] ?? ''));
        if ($m === '') {
            $e['email'] = 'Email is required.';
        } elseif (!filter_var($m, FILTER_VALIDATE_EMAIL) || strlen($m) > 100) {
            $e['email'] = 'Enter a valid email address.';
        } elseif ((new User())->exists('email', $m, $excludeId)) {
            $e['email'] = 'This email is already registered.';
        }

        $p = (string)($in['password'] ?? '');
        if ($passwordRequired || $p !== '') {
            if (strlen($p) < 8 || !preg_match('/[A-Za-z]/', $p) || !preg_match('/\d/', $p)) {
                $e['password'] = 'Password must be at least 8 characters and contain a letter and a digit.';
            } elseif (isset($in['password_confirm']) && $p !== (string)$in['password_confirm']) {
                $e['password_confirm'] = 'Passwords do not match.';
            }
        }

        if ($roleEditable && !in_array($in['role'] ?? '', ['admin', 'staff'], true)) {
            $e['role'] = 'Choose a valid role.';
        }
        return $e;
    }

    public static function bike(array $in, int $excludeId = 0): array
    {
        $e = [];
        $code = strtoupper(trim((string)($in['code'] ?? '')));
        if ($code === '') {
            $e['code'] = 'Bike code is required.';
        } elseif (!preg_match('/^[A-Z0-9-]{3,20}$/', $code)) {
            $e['code'] = '3-20 characters: capital letters, digits and hyphen (e.g. BK-009).';
        } elseif ((new Bike())->codeExists($code, $excludeId)) {
            $e['code'] = 'A bike with this code already exists.';
        }

        $model = trim((string)($in['model'] ?? ''));
        if ($model === '' || mb_strlen($model) < 2 || mb_strlen($model) > 80) {
            $e['model'] = 'Model is required (2-80 characters).';
        }

        $rate = $in['hourly_rate'] ?? '';
        if (!is_numeric($rate) || (float)$rate <= 0 || (float)$rate > 1000000) {
            $e['hourly_rate'] = 'Hourly rate must be a number greater than 0.';
        }

        if (!in_array($in['status'] ?? '', Bike::STATUSES, true)) {
            $e['status'] = 'Choose a valid status.';
        }
        return $e;
    }

    public static function customer(array $in, int $excludeId = 0): array
    {
        $e = [];
        $n = trim((string)($in['full_name'] ?? ''));
        if ($n === '') {
            $e['full_name'] = 'Full name is required.';
        } elseif (mb_strlen($n) < 3 || mb_strlen($n) > 100 || !preg_match("/^[\\p{L} .'-]+$/u", $n)) {
            $e['full_name'] = '3-100 characters; letters, spaces, . \' - only.';
        }

        $p = trim((string)($in['phone'] ?? ''));
        if (!preg_match('/^\+?[0-9 ]{7,15}$/', $p)) {
            $e['phone'] = 'Enter a valid phone number (7-15 digits, optional +).';
        }

        $m = trim((string)($in['email'] ?? ''));
        if ($m === '') {
            $e['email'] = 'Email is required.';
        } elseif (!filter_var($m, FILTER_VALIDATE_EMAIL) || strlen($m) > 100) {
            $e['email'] = 'Enter a valid email address.';
        } elseif ((new Customer())->emailExists($m, $excludeId)) {
            $e['email'] = 'A customer with this email already exists.';
        }
        return $e;
    }

    /** $editing: bike is locked and not re-validated for availability. */
    public static function rental(array $in, bool $editing = false): array
    {
        $e = [];
        if (!$editing) {
            $bikeId = filter_var($in['bike_id'] ?? '', FILTER_VALIDATE_INT);
            $bike = $bikeId ? (new Bike())->find($bikeId) : null;
            if (!$bike) {
                $e['bike_id'] = 'Choose a bike.';
            } elseif ($bike['status'] !== 'available') {
                $e['bike_id'] = 'This bike is not available for rent.';
            }
        }

        $cid = filter_var($in['customer_id'] ?? '', FILTER_VALIDATE_INT);
        if (!$cid || !(new Customer())->find($cid)) {
            $e['customer_id'] = 'Choose a customer.';
        }

        $start = self::parseDateTime((string)($in['start_time'] ?? ''));
        if ($start === null) {
            $e['start_time'] = 'Enter a valid start date and time.';
        } elseif (!$editing && $start > time() + 86400) {
            $e['start_time'] = 'Start time cannot be more than 24 hours in the future.';
        }
        return $e;
    }

    /** Accepts "2026-11-21T09:30" (datetime-local) or "2026-11-21 09:30[:00]". */
    public static function parseDateTime(string $v): ?int
    {
        $v = trim(str_replace('T', ' ', $v));
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/', $v)) {
            return null;
        }
        $t = strtotime($v);
        return $t === false ? null : $t;
    }

    public static function toSql(string $v): string
    {
        return date('Y-m-d H:i:s', (int)self::parseDateTime($v));
    }
}
