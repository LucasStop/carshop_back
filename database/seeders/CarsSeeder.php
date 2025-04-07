<?php

namespace Database\Seeders;

use App\Domain\Cars\Entities\Cars;
use Illuminate\Database\Seeder;

class CarsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cars = [
            [
                'model_id' => 1, // Toyota Corolla
                'vin' => 'JT2BF22K1W0123456',
                'color' => 'Branco',
                'manufacture_year' => 2023,
                'mileage' => 0,
                'status' => 'available',
                'inclusion_date' => now(),
            ],
            [
                'model_id' => 2, // Volkswagen Golf
                'vin' => 'WVWZZZ1KZAW123456',
                'color' => 'Preto',
                'manufacture_year' => 2023,
                'mileage' => 0,
                'status' => 'available',
                'inclusion_date' => now(),
            ],
            [
                'model_id' => 3, // Ford Mustang
                'vin' => '1ZVBP8CF2E5234567',
                'color' => 'Vermelho',
                'manufacture_year' => 2023,
                'mileage' => 0,
                'status' => 'available',
                'inclusion_date' => now(),
            ],
            [
                'model_id' => 4, // BMW X5
                'vin' => 'WBAKJ4C52KL345678',
                'color' => 'Azul',
                'manufacture_year' => 2023,
                'mileage' => 0,
                'status' => 'available',
                'inclusion_date' => now(),
            ],
            [
                'model_id' => 5, // Honda Civic
                'vin' => '2HGFC2F55MH456789',
                'color' => 'Prata',
                'manufacture_year' => 2023,
                'mileage' => 0,
                'status' => 'available',
                'inclusion_date' => now(),
            ],
        ];

        foreach ($cars as $car) {
            Cars::create($car);
        }
    }
}
