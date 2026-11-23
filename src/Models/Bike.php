<?php
declare(strict_types=1);

namespace App\Models;

final class Bike extends BaseModel
{
    public const STATUSES = ['available', 'rented', 'maintenance'];

    /** Search by code/model and optional status. Used by the page AND the JSON API. */
    public function search(string $term = '', string $status = ''): array
    {
        $sql = 'SELECT bike_id, code, model, hourly_rate, status FROM bikes WHERE 1 = 1';
        $p = [];
        if ($term !== '') {
            $sql .= ' AND (code LIKE :t1 OR model LIKE :t2)';
            $like = '%' . addcslashes($term, '%_\\') . '%';
            $p[':t1'] = $like;
            $p[':t2'] = $like;
        }
        if (in_array($status, self::STATUSES, true)) {
            $sql .= ' AND status = :s';
            $p[':s'] = $status;
        }
        return $this->all($sql . ' ORDER BY code', $p);
    }

    public function find(int $id): ?array
    {
        return $this->one('SELECT * FROM bikes WHERE bike_id = :id', [':id' => $id]);
    }

    public function available(): array
    {
        return $this->all("SELECT bike_id, code, model, hourly_rate FROM bikes WHERE status = 'available' ORDER BY code");
    }

    public function codeExists(string $code, int $excludeId = 0): bool
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM bikes WHERE code = :c AND bike_id <> :id',
            [':c' => $code, ':id' => $excludeId]) > 0;
    }

    public function create(array $d): int
    {
        $this->run('INSERT INTO bikes (code, model, hourly_rate, status) VALUES (:c, :m, :r, :s)',
            [':c' => $d['code'], ':m' => $d['model'], ':r' => $d['hourly_rate'], ':s' => $d['status']]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): int
    {
        return $this->run('UPDATE bikes SET code=:c, model=:m, hourly_rate=:r, status=:s WHERE bike_id=:id',
            [':c' => $d['code'], ':m' => $d['model'], ':r' => $d['hourly_rate'], ':s' => $d['status'], ':id' => $id]);
    }

    public function delete(int $id): int
    {
        return $this->run('DELETE FROM bikes WHERE bike_id = :id', [':id' => $id]);
    }

    public function countByStatus(): array
    {
        $rows = $this->all('SELECT status, COUNT(*) AS n FROM bikes GROUP BY status');
        $out = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $r) {
            $out[$r['status']] = (int)$r['n'];
        }
        return $out;
    }
}
