<?php

namespace Database\Seeders;

use App\Domain\Brands\Entities\Brands;
use Illuminate\Database\Seeder;

class BrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Toyota',
                'country_origin' => 'Japão',
            ],
            [
                'name' => 'Volkswagen',
                'country_origin' => 'Alemanha',
            ],
            [
                'name' => 'Ford',
                'country_origin' => 'Estados Unidos',
            ],
            [
                'name' => 'BMW',
                'country_origin' => 'Alemanha',
            ],
            [
                'name' => 'Honda',
                'country_origin' => 'Japão',
            ],
        ];

        foreach ($brands as $brand) {
            Brands::create($brand);
        }
    }
}
