<?php

use App\Domain\Cars\Controllers\CarsController;
use Illuminate\Support\Facades\Route;

// Rotas públicas (sem autenticação)
Route::controller(CarsController::class)
    ->prefix('cars')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::get('/model/{modelId}', 'byModel');
        Route::get('/status/{status}', 'byStatus');
        Route::get('/price-range', 'byPriceRange');
    });

// Rotas protegidas (requer autenticação)
Route::controller(CarsController::class)
    ->prefix('cars')
    ->middleware('auth:api')
    ->group(function () {
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
