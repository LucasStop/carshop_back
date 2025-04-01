<?php

namespace App\Domain\Customers;

use Illuminate\Support\ServiceProvider;

class CustomersServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(Providers\RouteServiceProvider::class);
    }

    public function boot()
    {
        // Carregamento de outras configurações se necessário
    }
}
