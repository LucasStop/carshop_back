<?php

use App\Domain\Brands\Controllers\BrandsController;
use Illuminate\Support\Facades\Route;

Route::controller(BrandsController::class)
    ->prefix('brands')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{brand_id}', 'show');
        Route::put('/{brand_id}', 'update');
        Route::delete('/{brand_id}', 'delete');
    });
