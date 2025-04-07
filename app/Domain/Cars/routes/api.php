<?php

use App\Domain\Cars\Controllers\CarsController;
use Illuminate\Support\Facades\Route;

Route::controller(CarsController::class)
    ->prefix('cars')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
        Route::get('/model/{modelId}', 'byModel');
        Route::get('/status/{status}', 'byStatus');
    });
