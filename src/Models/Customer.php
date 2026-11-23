<?php
declare(strict_types=1);

namespace App\Models;

final class Customer extends BaseModel
{
    /** LEFT JOIN so customers with no rentals still appear (count 0). */
    public function search(string $term = ''): array
    {
        $sql = 'SELECT c.customer_id, c.full_name, c.phone, c.email, COUNT(r.rental_id) AS rental_count
                FROM customers c LEFT JOIN rentals r ON r.customer_id = c.customer_id';
        $p = [];
        if ($term !== '') {
            $sql .= ' WHERE c.full_name LIKE :t1 OR c.email LIKE :t2 OR c.phone LIKE :t3';
            $like = '%' . addcslashes($term, '%_\\') . '%';
            $p = [':t1' => $like, ':t2' => $like, ':t3' => $like];
        }
        $sql .= ' GROUP BY c.customer_id, c.full_name, c.phone, c.email ORDER BY c.full_name';
        return $this->all($sql, $p);
    }

    public function all_customers(): array
    {
        return $this->all('SELECT customer_id, full_name FROM customers ORDER BY full_name');
    }

    public function find(int $id): ?array
    {
        return $this->one('SELECT * FROM customers WHERE customer_id = :id', [':id' => $id]);
    }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM customers WHERE email = :e AND customer_id <> :id',
            [':e' => $email, ':id' => $excludeId]) > 0;
    }

    public function create(array $d): int
    {
        $this->run('INSERT INTO customers (full_name, phone, email) VALUES (:n, :p, :e)',
            [':n' => $d['full_name'], ':p' => $d['phone'], ':e' => $d['email']]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): int
    {
        return $this->run('UPDATE customers SET full_name=:n, phone=:p, email=:e WHERE customer_id=:id',
            [':n' => $d['full_name'], ':p' => $d['phone'], ':e' => $d['email'], ':id' => $id]);
    }

    public function delete(int $id): int
    {
        return $this->run('DELETE FROM customers WHERE customer_id = :id', [':id' => $id]);
    }

    public function count(): int
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM customers');
    }
}
