<?php

namespace Database\Seeders;

use App\Domain\Models\Entities\Models;
use Illuminate\Database\Seeder;

class ModelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
            [
                'brand_id' => 1, // Toyota
                'name' => 'Corolla',
                'year_model' => 2023,
                'engine' => '2.0',
                'power' => 170,
                'base_price' => 120000.00,
                'quantity' => 10,
            ],
            [
                'brand_id' => 2, // Volkswagen
                'name' => 'Golf',
                'year_model' => 2023,
                'engine' => '1.4 TSI',
                'power' => 150,
                'base_price' => 140000.00,
                'quantity' => 8,
            ],
            [
                'brand_id' => 3, // Ford
                'name' => 'Mustang',
                'year_model' => 2023,
                'engine' => '5.0 V8',
                'power' => 480,
                'base_price' => 500000.00,
                'quantity' => 3,
            ],
            [
                'brand_id' => 4, // BMW
                'name' => 'X5',
                'year_model' => 2023,
                'engine' => '3.0 Turbo',
                'power' => 340,
                'base_price' => 550000.00,
                'quantity' => 5,
            ],
            [
                'brand_id' => 5, // Honda
                'name' => 'Civic',
                'year_model' => 2023,
                'engine' => '2.0',
                'power' => 155,
                'base_price' => 130000.00,
                'quantity' => 12,
            ],
        ];

        foreach ($models as $model) {
            Models::create($model);
        }
    }
}
