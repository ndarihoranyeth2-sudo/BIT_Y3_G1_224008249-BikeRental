<?php
declare(strict_types=1);

namespace App\Models;

final class User extends BaseModel
{
    public function all_users(): array
    {
        return $this->all('SELECT user_id, username, email, role, created_at FROM users ORDER BY username');
    }

    public function find(int $id): ?array
    {
        return $this->one('SELECT user_id, username, email, role, created_at FROM users WHERE user_id = :id', [':id' => $id]);
    }

    public function findByUsername(string $username): ?array
    {
        return $this->one('SELECT * FROM users WHERE username = :u', [':u' => $username]);
    }

    /** Uniqueness check; $excludeId lets a record keep its own value while editing. */
    public function exists(string $field, string $value, int $excludeId = 0): bool
    {
        if (!in_array($field, ['username', 'email'], true)) {   // column names cannot be bound -> whitelist
            throw new \InvalidArgumentException('Bad field');
        }
        return (int)$this->scalar("SELECT COUNT(*) FROM users WHERE $field = :v AND user_id <> :id",
            [':v' => $value, ':id' => $excludeId]) > 0;
    }

    public function create(string $username, string $email, string $password, string $role): int
    {
        $this->run('INSERT INTO users (username, email, password_hash, role) VALUES (:u, :e, :p, :r)', [
            ':u' => $username, ':e' => $email,
            ':p' => password_hash($password, PASSWORD_DEFAULT), ':r' => $role,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $username, string $email, string $role, ?string $password): int
    {
        if ($password !== null && $password !== '') {
            return $this->run('UPDATE users SET username=:u, email=:e, role=:r, password_hash=:p WHERE user_id=:id', [
                ':u' => $username, ':e' => $email, ':r' => $role,
                ':p' => password_hash($password, PASSWORD_DEFAULT), ':id' => $id,
            ]);
        }
        return $this->run('UPDATE users SET username=:u, email=:e, role=:r WHERE user_id=:id',
            [':u' => $username, ':e' => $email, ':r' => $role, ':id' => $id]);
    }

    public function delete(int $id): int
    {
        return $this->run('DELETE FROM users WHERE user_id = :id', [':id' => $id]);
    }

    public function countAdmins(): int
    {
        return (int)$this->scalar("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    }
}
