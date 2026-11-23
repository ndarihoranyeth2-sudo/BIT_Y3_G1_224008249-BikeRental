<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Bike;
use App\Support\Auth;

/** JSON endpoint: GET index.php?route=api-bikes&search=trek&status=available */
final class BikeApiController
{
    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['message' => 'Authentication required.']);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Method not allowed.']);
            return;
        }

        $search = trim((string)($_GET['search'] ?? ''));
        $status = (string)($_GET['status'] ?? '');
        if ($status !== '' && !in_array($status, Bike::STATUSES, true)) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid status filter.']);
            return;
        }
        if (mb_strlen($search) > 50) {
            http_response_code(400);
            echo json_encode(['message' => 'Search term too long (max 50).']);
            return;
        }

        $rows = (new Bike())->search($search, $status);
        $data = array_map(static fn(array $b) => [
            'id'          => (int)$b['bike_id'],
            'code'        => $b['code'],
            'model'       => $b['model'],
            'hourly_rate' => (float)$b['hourly_rate'],
            'status'      => $b['status'],
        ], $rows);
        echo json_encode(['data' => $data, 'meta' => ['count' => count($data)]]);
    }
}
