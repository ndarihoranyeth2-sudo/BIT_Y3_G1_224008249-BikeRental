<?php
declare(strict_types=1);

namespace App\Models;

use App\Support\Database;
use PDO;

/** Models own SQL (always prepared) and return plain arrays. No HTML, no redirects. */
abstract class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::pdo();
    }

    protected function one(string $sql, array $params = []): ?array
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetch() ?: null;
    }

    protected function all(string $sql, array $params = []): array
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    protected function run(string $sql, array $params = []): int
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    protected function scalar(string $sql, array $params = []): mixed
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchColumn();
    }
}
