<?php

use App\Domain\Brands\Controllers\BrandsController;
use Illuminate\Support\Facades\Route;

Route::controller(BrandsController::class)
    ->prefix('brands')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
