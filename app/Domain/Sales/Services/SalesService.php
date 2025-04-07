<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Entities\Sales;
use Illuminate\Database\Eloquent\Collection;
use App\Domain\Cars\Services\CarsService;

class SalesService
{
    public function __construct(
        private Sales $entity,
        private CarsService $carsService
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->all();
    }

    public function find($id): ?Sales
    {
        return $this->entity->find($id);
    }

    public function create(array $data): Sales
    {
        $sale = $this->entity->create($data);
        
        // Atualiza o status do carro para 'sold'
        $car = $this->carsService->find($data['car_id']);
        if ($car) {
            $this->carsService->update($data['car_id'], ['status' => 'sold']);
        }
        
        return $sale;
    }

    public function update($id, array $data): bool
    {
        return $this->entity->find($id)->update($data);
    }

    public function delete($id): bool
    {
        $sale = $this->entity->find($id);
        
        // Atualiza o status do carro para 'available' novamente
        if ($sale) {
            $this->carsService->update($sale->car_id, ['status' => 'available']);
        }
        
        return $sale->delete();
    }

    public function findByCustomer($customerId): Collection
    {
        return $this->entity->where('customer_id', $customerId)->get();
    }

    public function findByEmployee($employeeId): Collection
    {
        return $this->entity->where('employee_id', $employeeId)->get();
    }

    public function findByCar($carId): ?Sales
    {
        return $this->entity->where('car_id', $carId)->first();
    }
}
