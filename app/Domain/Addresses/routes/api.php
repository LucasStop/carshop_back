<?php

use App\Domain\Addresses\Controllers\AddressesController;
use Illuminate\Support\Facades\Route;

Route::controller(AddressesController::class)
    ->prefix('addresses')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
        Route::get('/user/{userId}', 'byUser');
    });