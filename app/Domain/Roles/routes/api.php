<?php

use App\Domain\Roles\Controllers\RolesController;
use Illuminate\Support\Facades\Route;

Route::controller(RolesController::class)
    ->prefix('roles')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
