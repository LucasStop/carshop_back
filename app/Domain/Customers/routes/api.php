<?php

use App\Domain\Customers\Controllers\CustomersController;
use Illuminate\Support\Facades\Route;

Route::controller(CustomersController::class)
    ->prefix('customers')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
        Route::get('/cpf/{cpf}', 'findByCpf');
        Route::get('/top-customers', 'topCustomers');
    });
