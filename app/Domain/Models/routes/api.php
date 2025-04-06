<?php

use App\Domain\Models\Controllers\ModelsController;
use Illuminate\Support\Facades\Route;

Route::controller(ModelsController::class)
    ->prefix('models')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
        Route::get('/brand/{brandId}', 'byBrand');
    });
