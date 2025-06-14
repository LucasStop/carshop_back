<?php

use App\Domain\Brands\Controllers\BrandsController;
use Illuminate\Support\Facades\Route;

// Rotas públicas (sem autenticação)
Route::controller(BrandsController::class)
    ->prefix('brands')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::get('/popular', 'popularBrands');
    });

// Rotas protegidas (requer autenticação)
Route::controller(BrandsController::class)
    ->prefix('brands')
    ->middleware('auth:api')
    ->group(function () {
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
