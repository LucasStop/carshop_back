<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Executar os seeders na ordem correta para respeitar as relações
        $this->call([
            BrandsSeeder::class,        // Primeiro as marcas
            ModelsSeeder::class,        // Depois os modelos (que precisam de marcas)
            CarsSeeder::class,          // Depois os carros (que precisam de modelos)
            CustomersSeeder::class,     // Clientes (independente)
            EmployeesSeeder::class,     // Funcionários (independente)
            SalesSeeder::class,         // Por último as vendas (que precisam de carros, clientes e funcionários)
        ]);
    }
}
