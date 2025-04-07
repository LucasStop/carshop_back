<?php

namespace Database\Seeders;

use App\Domain\Employees\Entities\Employees;
use Illuminate\Database\Seeder;

class EmployeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'name' => 'Roberto Almeida',
                'position' => 'Vendedor',
                'email' => 'roberto.almeida@carshop.com',
                'phone' => '(11) 98888-7777',
                'cpf' => '111.222.333-44',
                'rg' => '11.222.333-4',
                'birth_date' => '1982-04-15',
                'address' => 'Rua dos Vendedores',
                'number' => '100',
                'complement' => 'Apto 10',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-111',
                'hire_date' => '2020-01-15',
                'salary' => 3500.00,
            ],
            [
                'name' => 'Fernanda Costa',
                'position' => 'Gerente',
                'email' => 'fernanda.costa@carshop.com',
                'phone' => '(11) 97777-6666',
                'cpf' => '222.333.444-55',
                'rg' => '22.333.444-5',
                'birth_date' => '1975-08-20',
                'address' => 'Avenida dos Gerentes',
                'number' => '200',
                'complement' => 'Casa',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-222',
                'hire_date' => '2018-05-10',
                'salary' => 8000.00,
            ],
            [
                'name' => 'Lucas Martins',
                'position' => 'Vendedor',
                'email' => 'lucas.martins@carshop.com',
                'phone' => '(11) 96666-5555',
                'cpf' => '333.444.555-66',
                'rg' => '33.444.555-6',
                'birth_date' => '1990-11-03',
                'address' => 'Rua das Vendas',
                'number' => '300',
                'complement' => 'Apto 30',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-333',
                'hire_date' => '2021-02-20',
                'salary' => 3000.00,
            ],
            [
                'name' => 'Juliana Ribeiro',
                'position' => 'Financeiro',
                'email' => 'juliana.ribeiro@carshop.com',
                'phone' => '(11) 95555-4444',
                'cpf' => '444.555.666-77',
                'rg' => '44.555.666-7',
                'birth_date' => '1988-07-12',
                'address' => 'Alameda Financeira',
                'number' => '400',
                'complement' => 'Sala 40',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-444',
                'hire_date' => '2019-09-05',
                'salary' => 4500.00,
            ],
            [
                'name' => 'Ricardo Santos',
                'position' => 'Vendedor',
                'email' => 'ricardo.santos@carshop.com',
                'phone' => '(11) 94444-3333',
                'cpf' => '555.666.777-88',
                'rg' => '55.666.777-8',
                'birth_date' => '1985-02-28',
                'address' => 'Travessa dos Automóveis',
                'number' => '500',
                'complement' => 'Apto 50',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-555',
                'hire_date' => '2020-08-15',
                'salary' => 3200.00,
            ],
        ];

        foreach ($employees as $employee) {
            Employees::create($employee);
        }
    }
}
