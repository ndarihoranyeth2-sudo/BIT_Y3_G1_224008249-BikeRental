<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Services\Validator;
use App\Support\{Auth, Csrf, Flash, View};

final class AuthController
{
    public function login(): void
    {
        if (Auth::check()) redirect('dashboard');
        $errors = [];
        $old = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
            $old = ['username' => trim((string)($_POST['username'] ?? ''))];
            $errors = Validator::login($_POST);
            if (!$errors) {
                if (Auth::attempt($old['username'], (string)$_POST['password'])) {
                    Flash::success('Welcome back, ' . $old['username'] . '!');
                    redirect('dashboard');
                }
                // Same message for unknown user and wrong password (no user enumeration).
                $errors['form'] = 'Invalid username or password.';
            }
        }
        View::render('auth/login', compact('errors', 'old'));
    }

    public function register(): void
    {
        if (Auth::check()) redirect('dashboard');
        $errors = [];
        $old = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
            $old = [
                'username' => trim((string)($_POST['username'] ?? '')),
                'email'    => trim((string)($_POST['email'] ?? '')),
            ];
            // Self-registration always creates a 'staff' account; only an admin can create admins.
            $errors = Validator::user($_POST, 0, true, false);
            if (!$errors) {
                (new User())->create($old['username'], $old['email'], (string)$_POST['password'], 'staff');
                Flash::success('Account created. You can now log in.');
                redirect('login');
            }
        }
        View::render('auth/register', compact('errors', 'old'));
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('POST required');
        }
        Csrf::verify();
        Auth::logout();
        session_start();                 // fresh session for the flash message
        Flash::success('You have been logged out.');
        redirect('login');
    }
}
