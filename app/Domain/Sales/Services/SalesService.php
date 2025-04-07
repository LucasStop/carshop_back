<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Entities\Sales;
use App\Domain\Cars\Services\CarsService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class SalesService
{
    public function __construct(
        private Sales $entity,
        private CarsService $carsService
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->with(['car', 'customer', 'employee'])->get();
    }

    public function find($id): ?Sales
    {
        return $this->entity->with(['car', 'customer', 'employee'])->find($id);
    }

    public function create(array $data): Sales
    {
        DB::beginTransaction();
        try {
            // Criar a venda
            $sale = $this->entity->create($data);
            
            // Atualiza o status do carro para 'sold'
            $car = $this->carsService->find($data['car_id']);
            if ($car) {
                $this->carsService->update($data['car_id'], ['status' => 'sold']);
            } else {
                throw new Exception("Carro com ID {$data['car_id']} não encontrado");
            }
            
            DB::commit();
            return $sale;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao registrar venda: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, array $data): bool
    {
        DB::beginTransaction();
        try {
            $sale = $this->entity->find($id);
            
            if (!$sale) {
                throw new Exception("Venda com ID {$id} não encontrada");
            }
            
            // Se o carro foi alterado, atualize os status
            if (isset($data['car_id']) && $data['car_id'] != $sale->car_id) {
                // O carro anterior volta a estar disponível
                $this->carsService->update($sale->car_id, ['status' => 'available']);
                
                // O novo carro passa a estar vendido
                $this->carsService->update($data['car_id'], ['status' => 'sold']);
            }
            
            $result = $sale->update($data);
            
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar venda: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id): bool
    {
        DB::beginTransaction();
        try {
            $sale = $this->entity->find($id);
            
            if (!$sale) {
                throw new Exception("Venda com ID {$id} não encontrada");
            }
            
            // Atualiza o status do carro para 'available' novamente
            if ($sale->car_id) {
                $this->carsService->update($sale->car_id, ['status' => 'available']);
            }
            
            $result = $sale->delete();
            
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir venda: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findByCustomer($customerId): Collection
    {
        return $this->entity->with(['car', 'car.model', 'employee'])
            ->where('customer_id', $customerId)
            ->get();
    }

    public function findByEmployee($employeeId): Collection
    {
        return $this->entity->with(['car', 'car.model', 'customer'])
            ->where('employee_id', $employeeId)
            ->get();
    }

    public function findByCar($carId): ?Sales
    {
        return $this->entity->with(['customer', 'employee'])
            ->where('car_id', $carId)
            ->first();
    }
    
    /**
     * Obtém resumo de vendas por período
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getSalesSummary(string $startDate, string $endDate): array
    {
        try {
            $sales = $this->entity
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->get();
            
            $totalSales = $sales->count();
            $totalRevenue = $sales->sum('final_price');
            $averagePrice = $totalSales > 0 ? $totalRevenue / $totalSales : 0;
            
            return [
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
                'average_price' => $averagePrice
            ];
        } catch (Exception $e) {
            Log::error('Erro ao gerar resumo de vendas: ' . $e->getMessage());
            throw $e;
        }
    }
}
