<?php

namespace Database\Seeders;

use App\Domain\Sales\Entities\Sales;
use App\Domain\Cars\Entities\Cars;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primeiro obtemos 5 carros para serem vendidos
        $cars = Cars::where('status', 'available')->take(5)->get();
        
        if ($cars->count() < 5) {
            $this->command->info('Não há carros suficientes disponíveis para criar vendas. Certifique-se de que existam pelo menos 5 carros com status "available".');
            return;
        }
        
        $sales = [
            [
                'car_id' => $cars[0]->id,
                'customer_id' => 1, // João Silva
                'employee_id' => 1, // Roberto Almeida
                'sale_date' => '2023-03-15',
                'final_price' => 115000.00,
                'notes' => 'Venda à vista com desconto',
            ],
            [
                'car_id' => $cars[1]->id,
                'customer_id' => 2, // Maria Oliveira
                'employee_id' => 3, // Lucas Martins
                'sale_date' => '2023-03-18',
                'final_price' => 138500.00,
                'notes' => 'Financiamento em 48x',
            ],
            [
                'car_id' => $cars[2]->id,
                'customer_id' => 3, // Pedro Santos
                'employee_id' => 5, // Ricardo Santos
                'sale_date' => '2023-03-20',
                'final_price' => 495000.00,
                'notes' => 'Cliente antigo, compra recorrente',
            ],
            [
                'car_id' => $cars[3]->id,
                'customer_id' => 4, // Ana Souza
                'employee_id' => 1, // Roberto Almeida
                'sale_date' => '2023-03-22',
                'final_price' => 540000.00,
                'notes' => 'Venda com test drive estendido',
            ],
            [
                'car_id' => $cars[4]->id,
                'customer_id' => 5, // Carlos Pereira
                'employee_id' => 3, // Lucas Martins
                'sale_date' => '2023-03-25',
                'final_price' => 128500.00,
                'notes' => 'Indicação de outro cliente',
            ],
        ];

        foreach ($sales as $index => $sale) {
            // Criar a venda
            Sales::create($sale);
            
            // Atualizar o status do carro para 'sold'
            $cars[$index]->update(['status' => 'sold']);
        }
    }
}
