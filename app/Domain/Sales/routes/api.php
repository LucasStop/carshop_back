<?php

use App\Domain\Sales\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

Route::controller(SalesController::class)
    ->prefix('sales')
    ->middleware('auth:api')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
        Route::get('/customer/{customerId}', 'byCustomer');
        Route::get('/employee/{employeeId}', 'byEmployee');
        Route::get('/car/{carId}', 'byCar');
        Route::post('/summary', 'summary');
    });
