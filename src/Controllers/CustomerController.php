<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\{Customer, Rental};
use App\Services\Validator;
use App\Support\{Auth, Csrf, Flash, View};

final class CustomerController
{
    private Customer $customers;

    public function __construct()
    {
        Auth::require();
        $this->customers = new Customer();
    }

    public function index(): void
    {
        $search = trim((string)($_GET['search'] ?? ''));
        $items = $this->customers->search($search);
        View::render('customers/index', compact('items', 'search'));
    }

    public function show(): void
    {
        $customer = $this->customers->find((int)($_GET['id'] ?? 0));
        if (!$customer) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }
        $rentals = (new Rental())->forCustomer((int)$customer['customer_id']);
        View::render('customers/show', compact('customer', 'rentals'));
    }

    public function form(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $c = $id ? $this->customers->find($id) : null;
        if ($id && !$c) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }
        $old = $c ?? [];
        $errors = [];
        View::render('customers/form', compact('old', 'errors', 'id'));
    }

    public function save(): void
    {
        $this->postOnly();
        $id = (int)($_POST['customer_id'] ?? 0);
        $errors = Validator::customer($_POST, $id);
        if ($errors) {
            $old = $_POST;
            View::render('customers/form', compact('old', 'errors', 'id'));
            return;
        }
        $data = [
            'full_name' => trim($_POST['full_name']),
            'phone'     => trim($_POST['phone']),
            'email'     => trim($_POST['email']),
        ];
        if ($id) {
            if (!$this->customers->find($id)) { http_response_code(404); View::render('errors/404', []); return; }
            $this->customers->update($id, $data);
            Flash::success('Customer updated.');
        } else {
            $id = $this->customers->create($data);
            Flash::success('Customer added.');
        }
        redirect('customer-show', ['id' => $id]);
    }

    public function delete(): void
    {
        $this->postOnly();
        $id = (int)($_POST['id'] ?? 0);
        try {
            $n = $this->customers->delete($id);
            $n ? Flash::success('Customer deleted.') : Flash::error('Customer not found.');
        } catch (\PDOException $e) {
            Flash::error('This customer cannot be deleted because they have rental records.');
        }
        redirect('customers');
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
