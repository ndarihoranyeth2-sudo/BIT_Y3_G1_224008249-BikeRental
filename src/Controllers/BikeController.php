<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Bike;
use App\Services\Validator;
use App\Support\{Auth, Csrf, Flash, View};

final class BikeController
{
    private Bike $bikes;

    public function __construct()
    {
        Auth::require();
        $this->bikes = new Bike();
    }

    public function index(): void
    {
        $search = trim((string)($_GET['search'] ?? ''));
        $status = (string)($_GET['status'] ?? '');
        $items  = $this->bikes->search($search, $status);   // server-rendered fallback
        View::render('bikes/index', compact('items', 'search', 'status'));
    }

    public function show(): void
    {
        $bike = $this->bikes->find((int)($_GET['id'] ?? 0));
        if (!$bike) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }
        View::render('bikes/show', compact('bike'));
    }

    /** Create (no id) or edit (?id=N) form. */
    public function form(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $bike = $id ? $this->bikes->find($id) : null;
        if ($id && !$bike) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }
        $old = $bike ?? ['status' => 'available'];
        $errors = [];
        View::render('bikes/form', compact('old', 'errors', 'id'));
    }

    public function save(): void
    {
        $this->postOnly();
        $id = (int)($_POST['bike_id'] ?? 0);
        $errors = Validator::bike($_POST, $id);
        if ($errors) {
            $old = $_POST;
            View::render('bikes/form', compact('old', 'errors', 'id'));   // re-populate, no data loss
            return;
        }
        $data = [
            'code'        => strtoupper(trim($_POST['code'])),
            'model'       => trim($_POST['model']),
            'hourly_rate' => round((float)$_POST['hourly_rate'], 2),
            'status'      => $_POST['status'],
        ];
        if ($id) {
            if (!$this->bikes->find($id)) { http_response_code(404); View::render('errors/404', []); return; }
            $this->bikes->update($id, $data);
            Flash::success('Bike updated.');
        } else {
            $id = $this->bikes->create($data);
            Flash::success('Bike added.');
        }
        redirect('bike-show', ['id' => $id]);       // Post-Redirect-Get
    }

    public function delete(): void
    {
        $this->postOnly();
        $id = (int)($_POST['id'] ?? 0);
        try {
            $n = $this->bikes->delete($id);
            $n ? Flash::success('Bike deleted.') : Flash::error('Bike not found.');
        } catch (\PDOException $e) {
            Flash::error('This bike cannot be deleted because it has rental records.');
        }
        redirect('bikes');
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
