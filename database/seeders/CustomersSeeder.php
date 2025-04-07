<?php

namespace Database\Seeders;

use App\Domain\Customers\Entities\Customers;
use Illuminate\Database\Seeder;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'João Silva',
                'email' => 'joao.silva@example.com',
                'phone' => '(11) 98765-4321',
                'cpf' => '123.456.789-00',
                'rg' => '12.345.678-9',
                'birth_date' => '1985-05-15',
                'address' => 'Rua das Flores',
                'number' => '123',
                'complement' => 'Apto 101',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567',
            ],
            [
                'name' => 'Maria Oliveira',
                'email' => 'maria.oliveira@example.com',
                'phone' => '(11) 91234-5678',
                'cpf' => '987.654.321-00',
                'rg' => '98.765.432-1',
                'birth_date' => '1990-10-20',
                'address' => 'Avenida Paulista',
                'number' => '1000',
                'complement' => 'Sala 200',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01311-200',
            ],
            [
                'name' => 'Pedro Santos',
                'email' => 'pedro.santos@example.com',
                'phone' => '(21) 99876-5432',
                'cpf' => '456.789.123-00',
                'rg' => '45.678.912-3',
                'birth_date' => '1978-03-25',
                'address' => 'Rua da Praia',
                'number' => '500',
                'complement' => 'Casa',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'zip_code' => '22000-123',
            ],
            [
                'name' => 'Ana Souza',
                'email' => 'ana.souza@example.com',
                'phone' => '(31) 98765-1234',
                'cpf' => '789.123.456-00',
                'rg' => '78.912.345-6',
                'birth_date' => '1995-12-10',
                'address' => 'Rua das Árvores',
                'number' => '50',
                'complement' => 'Bloco B',
                'city' => 'Belo Horizonte',
                'state' => 'MG',
                'zip_code' => '30000-789',
            ],
            [
                'name' => 'Carlos Pereira',
                'email' => 'carlos.pereira@example.com',
                'phone' => '(41) 99123-4567',
                'cpf' => '321.654.987-00',
                'rg' => '32.165.498-7',
                'birth_date' => '1980-08-30',
                'address' => 'Alameda dos Pinheiros',
                'number' => '300',
                'complement' => 'Casa 2',
                'city' => 'Curitiba',
                'state' => 'PR',
                'zip_code' => '80000-456',
            ],
        ];

        foreach ($customers as $customer) {
            Customers::create($customer);
        }
    }
}
