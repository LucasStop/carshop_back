<?php

use App\Domain\Users\Controllers\UsersController;
use App\Domain\Users\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Rotas de autenticação
Route::controller(AuthController::class)
    ->prefix('auth')
    ->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/refresh', 'refresh')->middleware('auth.api');
        Route::get('/me', 'me')->middleware('auth.api');
    });

// Rotas de usuários
Route::controller(UsersController::class)
    ->prefix('users')
    ->middleware('auth.api') 
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
        Route::get('/role/{roleId}', 'byRole');
    });