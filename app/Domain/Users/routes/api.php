<?php

use App\Domain\Users\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::controller(UsersController::class)
    ->prefix('users')
    ->group(function () {
        Route::get('/roles', 'roles');
        Route::get('/resume', 'resume');
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::post('/update/{hash}', 'update');
        Route::delete('/{hash}', 'destroy');
    });
