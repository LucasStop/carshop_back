<?php

namespace App\Domain\Cars\Services;

use App\Domain\Cars\Entities\Cars;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use Illuminate\Support\Facades\Log;

class CarsService
{
    public function __construct(
        private Cars $entity
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->with(['model', 'model.brand'])->get();
    }

    public function find($id): ?Cars
    {
        return $this->entity->with(['model', 'model.brand'])->find($id);
    }

    public function create(array $data): Cars
    {
        try {
            $car = $this->entity->create($data);
            return $car;
        } catch (Exception $e) {
            Log::error('Erro ao criar carro: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, array $data): bool
    {
        try {
            $car = $this->entity->find($id);
            
            if (!$car) {
                throw new Exception("Carro com ID {$id} não encontrado");
            }
            
            return $car->update($data);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar carro: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id): bool
    {
        try {
            $car = $this->entity->find($id);
            
            if (!$car) {
                throw new Exception("Carro com ID {$id} não encontrado");
            }
            
            return $car->delete();
        } catch (Exception $e) {
            Log::error('Erro ao excluir carro: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findByModel($modelId): Collection
    {
        return $this->entity->with(['model', 'model.brand'])
            ->where('model_id', $modelId)
            ->get();
    }

    public function findByStatus($status): Collection
    {
        return $this->entity->with(['model', 'model.brand'])
            ->where('status', $status)
            ->get();
    }
    
    /**
     * Busca carros por faixa de preço baseado no preço base do modelo
     * 
     * @param float $minPrice
     * @param float $maxPrice
     * @return Collection
     */
    public function findByPriceRange(float $minPrice, float $maxPrice): Collection
    {
        try {
            return $this->entity
                ->join('models', 'cars.model_id', '=', 'models.id')
                ->whereBetween('models.base_price', [$minPrice, $maxPrice])
                ->select('cars.*')
                ->with(['model', 'model.brand'])
                ->get();
        } catch (Exception $e) {
            Log::error('Erro na busca por faixa de preço: ' . $e->getMessage());
            throw $e;
        }
    }
}
