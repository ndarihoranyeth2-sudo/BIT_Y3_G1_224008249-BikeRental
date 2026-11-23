<?php
declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

final class Rental extends BaseModel
{
    public const STATUSES = ['active', 'completed', 'cancelled'];

    private const SELECT = 'SELECT r.rental_id, r.bike_id, r.customer_id, r.start_time, r.end_time, r.total_cost, r.status,
                                   b.code AS bike_code, b.model AS bike_model, b.hourly_rate,
                                   c.full_name AS customer_name, c.phone AS customer_phone
                            FROM rentals r
                            INNER JOIN bikes b     ON b.bike_id = r.bike_id
                            INNER JOIN customers c ON c.customer_id = r.customer_id';

    public function list(string $status = ''): array
    {
        if (in_array($status, self::STATUSES, true)) {
            return $this->all(self::SELECT . ' WHERE r.status = :s ORDER BY r.start_time DESC', [':s' => $status]);
        }
        return $this->all(self::SELECT . ' ORDER BY r.start_time DESC');
    }

    public function find(int $id): ?array
    {
        return $this->one(self::SELECT . ' WHERE r.rental_id = :id', [':id' => $id]);
    }

    public function forCustomer(int $customerId): array
    {
        return $this->all(self::SELECT . ' WHERE r.customer_id = :id ORDER BY r.start_time DESC', [':id' => $customerId]);
    }

    public function recent(int $n = 5): array
    {
        return $this->all(self::SELECT . ' ORDER BY r.start_time DESC LIMIT ' . (int)$n);
    }

    /** Billing rule: every started hour is billed, minimum one hour. */
    public static function cost(string $start, string $end, float $rate): float
    {
        $seconds = (new DateTimeImmutable($end))->getTimestamp() - (new DateTimeImmutable($start))->getTimestamp();
        $hours = max(1, (int)ceil($seconds / 3600));
        return round($hours * $rate, 2);
    }

    /**
     * Start a rental. The bike is claimed with a conditional UPDATE so two staff
     * members cannot rent the same bike at once. Returns new id, or null if the bike was not available.
     */
    public function start(int $bikeId, int $customerId, string $startTime): ?int
    {
        $this->db->beginTransaction();
        try {
            $claimed = $this->run("UPDATE bikes SET status = 'rented' WHERE bike_id = :b AND status = 'available'", [':b' => $bikeId]);
            if ($claimed === 0) {
                $this->db->rollBack();
                return null;
            }
            $this->run("INSERT INTO rentals (bike_id, customer_id, start_time, status) VALUES (:b, :c, :s, 'active')",
                [':b' => $bikeId, ':c' => $customerId, ':s' => $startTime]);
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** Edit customer and start time (bike is fixed once the rental exists). Recalculate cost if completed. */
    public function update(int $id, int $customerId, string $startTime): int
    {
        $r = $this->find($id);
        if (!$r) {
            return 0;
        }
        $cost = $r['total_cost'];
        if ($r['status'] === 'completed' && $r['end_time']) {
            $cost = self::cost($startTime, $r['end_time'], (float)$r['hourly_rate']);
        }
        return $this->run('UPDATE rentals SET customer_id=:c, start_time=:s, total_cost=:t WHERE rental_id=:id',
            [':c' => $customerId, ':s' => $startTime, ':t' => $cost, ':id' => $id]);
    }

    /** Close an active rental: stamp end time, compute cost, free the bike. */
    public function finish(int $id, string $endTime): ?float
    {
        $r = $this->find($id);
        if (!$r || $r['status'] !== 'active') {
            return null;
        }
        if (strtotime($endTime) < strtotime($r['start_time'])) {
            $endTime = $r['start_time'];
        }
        $cost = self::cost($r['start_time'], $endTime, (float)$r['hourly_rate']);
        $this->db->beginTransaction();
        try {
            $this->run("UPDATE rentals SET end_time=:e, total_cost=:t, status='completed' WHERE rental_id=:id AND status='active'",
                [':e' => $endTime, ':t' => $cost, ':id' => $id]);
            $this->run("UPDATE bikes SET status='available' WHERE bike_id=:b", [':b' => $r['bike_id']]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
        return $cost;
    }

    public function cancel(int $id): bool
    {
        $r = $this->find($id);
        if (!$r || $r['status'] !== 'active') {
            return false;
        }
        $this->db->beginTransaction();
        try {
            $this->run("UPDATE rentals SET status='cancelled', total_cost=0 WHERE rental_id=:id", [':id' => $id]);
            $this->run("UPDATE bikes SET status='available' WHERE bike_id=:b", [':b' => $r['bike_id']]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
        return true;
    }

    /** Delete a rental; if it was active the bike becomes available again. */
    public function delete(int $id): bool
    {
        $r = $this->find($id);
        if (!$r) {
            return false;
        }
        $this->db->beginTransaction();
        try {
            $this->run('DELETE FROM rentals WHERE rental_id = :id', [':id' => $id]);
            if ($r['status'] === 'active') {
                $this->run("UPDATE bikes SET status='available' WHERE bike_id=:b", [':b' => $r['bike_id']]);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
        return true;
    }

    public function stats(): array
    {
        return [
            'active'    => (int)$this->scalar("SELECT COUNT(*) FROM rentals WHERE status='active'"),
            'completed' => (int)$this->scalar("SELECT COUNT(*) FROM rentals WHERE status='completed'"),
            'revenue'   => (float)($this->scalar("SELECT COALESCE(SUM(total_cost),0) FROM rentals WHERE status='completed'") ?? 0),
        ];
    }
}
