<?php

use App\Domain\Uploads\Controllers\UploadsController;
use Illuminate\Support\Facades\Route;

Route::controller(UploadsController::class)
    ->prefix('uploads')
    ->group(function () {
        Route::post('/', 'store');
});