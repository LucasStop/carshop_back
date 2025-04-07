<?php

use App\Domain\Employees\Controllers\EmployeesController;
use Illuminate\Support\Facades\Route;

Route::controller(EmployeesController::class)
    ->prefix('employees')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
        Route::get('/cpf/{cpf}', 'findByCpf');
        Route::get('/position/{position}', 'findByPosition');
        Route::get('/top-sellers', 'topSellers');
    });
