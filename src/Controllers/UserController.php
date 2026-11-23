<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Services\Validator;
use App\Support\{Auth, Csrf, Flash, View};

/** Admin-only user management (full CRUD on the users entity). */
final class UserController
{
    private User $users;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->users = new User();
    }

    public function index(): void
    {
        $items = $this->users->all_users();
        View::render('users/index', compact('items'));
    }

    public function form(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $u = $id ? $this->users->find($id) : null;
        if ($id && !$u) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }
        $old = $u ?? ['role' => 'staff'];
        $errors = [];
        View::render('users/form', compact('old', 'errors', 'id'));
    }

    public function save(): void
    {
        $this->postOnly();
        $id = (int)($_POST['user_id'] ?? 0);
        $existing = $id ? $this->users->find($id) : null;
        if ($id && !$existing) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }
        $errors = Validator::user($_POST, $id, $id === 0);

        // Never demote the last admin.
        if (!$errors && $existing && $existing['role'] === 'admin' && $_POST['role'] !== 'admin'
            && $this->users->countAdmins() <= 1) {
            $errors['role'] = 'You cannot demote the last administrator.';
        }
        if ($errors) {
            $old = $_POST;
            View::render('users/form', compact('old', 'errors', 'id'));
            return;
        }

        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        if ($id) {
            $this->users->update($id, $username, $email, $_POST['role'], (string)($_POST['password'] ?? ''));
            if ($id === (int)Auth::user()['user_id']) {
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['role'] = $_POST['role'];
            }
            Flash::success('User updated.');
        } else {
            $this->users->create($username, $email, (string)$_POST['password'], $_POST['role']);
            Flash::success('User created.');
        }
        redirect('users');
    }

    public function delete(): void
    {
        $this->postOnly();
        $id = (int)($_POST['id'] ?? 0);
        $target = $this->users->find($id);
        if (!$target) {
            Flash::error('User not found.');
        } elseif ($id === (int)Auth::user()['user_id']) {
            Flash::error('You cannot delete your own account.');
        } elseif ($target['role'] === 'admin' && $this->users->countAdmins() <= 1) {
            Flash::error('You cannot delete the last administrator.');
        } else {
            $this->users->delete($id);
            Flash::success('User deleted.');
        }
        redirect('users');
    }

    private function postOnly(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('POST required');
        }
        Csrf::verify();
    }
}
