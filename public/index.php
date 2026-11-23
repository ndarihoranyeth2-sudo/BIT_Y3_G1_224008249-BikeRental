<?php
declare(strict_types=1);

/** Front controller: every request enters here. */
require __DIR__ . '/../src/bootstrap.php';

use App\Controllers\{AuthController, BikeApiController, BikeController, CustomerController,
    DashboardController, RentalController, UserController};
use App\Support\View;

$routes = [
    'login'           => [AuthController::class, 'login'],
    'register'        => [AuthController::class, 'register'],
    'logout'          => [AuthController::class, 'logout'],
    'dashboard'       => [DashboardController::class, 'index'],

    'bikes'           => [BikeController::class, 'index'],
    'bike-show'       => [BikeController::class, 'show'],
    'bike-form'       => [BikeController::class, 'form'],
    'bike-save'       => [BikeController::class, 'save'],
    'bike-delete'     => [BikeController::class, 'delete'],

    'customers'       => [CustomerController::class, 'index'],
    'customer-show'   => [CustomerController::class, 'show'],
    'customer-form'   => [CustomerController::class, 'form'],
    'customer-save'   => [CustomerController::class, 'save'],
    'customer-delete' => [CustomerController::class, 'delete'],

    'rentals'         => [RentalController::class, 'index'],
    'rental-show'     => [RentalController::class, 'show'],
    'rental-form'     => [RentalController::class, 'form'],
    'rental-save'     => [RentalController::class, 'save'],
    'rental-return'   => [RentalController::class, 'finish'],
    'rental-cancel'   => [RentalController::class, 'cancel'],
    'rental-delete'   => [RentalController::class, 'delete'],

    'users'           => [UserController::class, 'index'],
    'user-form'       => [UserController::class, 'form'],
    'user-save'       => [UserController::class, 'save'],
    'user-delete'     => [UserController::class, 'delete'],

    'api-bikes'       => [BikeApiController::class, 'index'],   // JSON
];

$route = (string)($_GET['route'] ?? 'dashboard');

if (!array_key_exists($route, $routes)) {
    http_response_code(404);
    View::render('errors/404');
    exit;
}

[$class, $method] = $routes[$route];
(new $class())->$method();
