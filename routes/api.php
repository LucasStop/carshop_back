<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Carrega as rotas de cada domínio
// Cada domínio já possui seu próprio prefixo 'api' no RouteServiceProvider

// Carregar rotas de usuários
if (file_exists(app_path('Domain/Users/routes/api.php'))) {
    require app_path('Domain/Users/routes/api.php');
}

// Carregar rotas de endereços
if (file_exists(app_path('Domain/Addresses/routes/api.php'))) {
    require app_path('Domain/Addresses/routes/api.php');
}

// Carregar rotas de marcas
if (file_exists(app_path('Domain/Brands/routes/api.php'))) {
    require app_path('Domain/Brands/routes/api.php');
}

// Carregar rotas de modelos
if (file_exists(app_path('Domain/Models/routes/api.php'))) {
    require app_path('Domain/Models/routes/api.php');
}

// Carregar rotas de carros
if (file_exists(app_path('Domain/Cars/routes/api.php'))) {
    require app_path('Domain/Cars/routes/api.php');
}

// Carregar rotas de vendas
if (file_exists(app_path('Domain/Sales/routes/api.php'))) {
    require app_path('Domain/Sales/routes/api.php');
}