<?php

use App\Domain\Users\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::controller(UsersController::class)
    ->prefix('users')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
        Route::get('/role/{roleId}', 'byRole');
    });