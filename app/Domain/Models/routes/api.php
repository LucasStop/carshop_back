<?php

use App\Domain\Models\Controllers\ModelsController;
use Illuminate\Support\Facades\Route;

// Rotas públicas (sem autenticação)
Route::controller(ModelsController::class)
    ->prefix('models')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::get('/brand/{brandId}', 'byBrand');
        Route::get('/low-stock', 'lowStock');
    });

// Rotas protegidas (requer autenticação)
Route::controller(ModelsController::class)
    ->prefix('models')
    ->middleware('auth:api')
    ->group(function () {
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
        
        // Rotas para gerenciamento de estoque
        Route::put('/{id}/quantity', 'updateQuantity');
        Route::put('/{id}/increment', 'incrementQuantity');
        Route::put('/{id}/decrement', 'decrementQuantity');
    });
