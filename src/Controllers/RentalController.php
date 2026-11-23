<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\{Bike, Customer, Rental};
use App\Services\Validator;
use App\Support\{Auth, Csrf, Flash, View};

final class RentalController
{
    private Rental $rentals;

    public function __construct()
    {
        Auth::require();
        $this->rentals = new Rental();
    }

    public function index(): void
    {
        $status = (string)($_GET['status'] ?? '');
        $items = $this->rentals->list($status);
        View::render('rentals/index', compact('items', 'status'));
    }

    public function show(): void
    {
        $rental = $this->rentals->find((int)($_GET['id'] ?? 0));
        if (!$rental) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }
        View::render('rentals/show', compact('rental'));
    }

    public function form(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $rental = $id ? $this->rentals->find($id) : null;
        if ($id && !$rental) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }
        $old = $rental
            ? ['customer_id' => $rental['customer_id'], 'bike_id' => $rental['bike_id'],
               'start_time' => date('Y-m-d\TH:i', strtotime($rental['start_time']))]
            : ['start_time' => date('Y-m-d\TH:i'), 'bike_id' => (int)($_GET['bike_id'] ?? 0)];
        $errors = [];
        $this->renderForm($old, $errors, $id, $rental);
    }

    public function save(): void
    {
        $this->postOnly();
        $id = (int)($_POST['rental_id'] ?? 0);
        $rental = $id ? $this->rentals->find($id) : null;
        if ($id && !$rental) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }
        $errors = Validator::rental($_POST, $id > 0);
        if ($errors) {
            $this->renderForm($_POST, $errors, $id, $rental);
            return;
        }
        $start = Validator::toSql((string)$_POST['start_time']);
        $customerId = (int)$_POST['customer_id'];

        if ($id) {
            $this->rentals->update($id, $customerId, $start);
            Flash::success('Rental updated.');
        } else {
            $id = $this->rentals->start((int)$_POST['bike_id'], $customerId, $start);
            if ($id === null) {                       // lost the race for this bike
                $errors['bike_id'] = 'This bike was just rented by someone else. Pick another.';
                $this->renderForm($_POST, $errors, 0, null);
                return;
            }
            Flash::success('Rental started.');
        }
        redirect('rental-show', ['id' => $id]);
    }

    public function finish(): void
    {
        $this->postOnly();
        $cost = $this->rentals->finish((int)($_POST['id'] ?? 0), date('Y-m-d H:i:s'));
        $cost === null
            ? Flash::error('Only active rentals can be returned.')
            : Flash::success('Bike returned. Total cost: ' . money($cost) . '.');
        redirect('rental-show', ['id' => (int)($_POST['id'] ?? 0)]);
    }

    public function cancel(): void
    {
        $this->postOnly();
        $ok = $this->rentals->cancel((int)($_POST['id'] ?? 0));
        $ok ? Flash::success('Rental cancelled; bike is available again.')
            : Flash::error('Only active rentals can be cancelled.');
        redirect('rental-show', ['id' => (int)($_POST['id'] ?? 0)]);
    }

    public function delete(): void
    {
        $this->postOnly();
        $ok = $this->rentals->delete((int)($_POST['id'] ?? 0));
        $ok ? Flash::success('Rental deleted.') : Flash::error('Rental not found.');
        redirect('rentals');
    }

    private function renderForm(array $old, array $errors, int $id, ?array $rental): void
    {
        $customers = (new Customer())->all_customers();
        $bikes = (new Bike())->available();
        View::render('rentals/form', compact('old', 'errors', 'id', 'rental', 'customers', 'bikes'));
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
