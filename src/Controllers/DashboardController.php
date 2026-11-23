<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\{Bike, Customer, Rental};
use App\Support\{Auth, View};

final class DashboardController
{
    public function index(): void
    {
        Auth::require();
        $bikeCounts = (new Bike())->countByStatus();
        $rentals    = new Rental();
        $stats      = $rentals->stats();
        $customers  = (new Customer())->count();
        $recent     = $rentals->recent(5);
        View::render('dashboard/index', compact('bikeCounts', 'stats', 'customers', 'recent'));
    }
}
