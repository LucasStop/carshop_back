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
    }    public function all(array $params = []): array
    {
        $query = $this->entity->with(['model', 'model.brand']);

        // Aplicar filtro de busca por VIN, cor ou marca/modelo
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('vin', 'LIKE', "%{$search}%")
                    ->orWhere('color', 'LIKE', "%{$search}%")
                    ->orWhereHas('model', function ($modelQuery) use ($search) {
                        $modelQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhereHas('brand', function ($brandQuery) use ($search) {
                                $brandQuery->where('name', 'LIKE', "%{$search}%");
                            });
                    });
            });
        }

        // Aplicar filtro por status
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        // Aplicar filtro por modelo
        if (!empty($params['model_id'])) {
            $query->where('model_id', $params['model_id']);
        }

        // Aplicar filtro por marca através do modelo
        if (!empty($params['brand_id'])) {
            $query->whereHas('model', function ($q) use ($params) {
                $q->where('brand_id', $params['brand_id']);
            });
        }

        // Aplicar filtro por ano de fabricação
        if (!empty($params['manufacture_year'])) {
            $query->where('manufacture_year', $params['manufacture_year']);
        }        // Aplicar filtro por faixa de preço
        if (!empty($params['min_price']) && !empty($params['max_price'])) {
            $query->whereBetween('price', [$params['min_price'], $params['max_price']]);
        }

        // Configurar paginação
        $perPage = $params['per_page'] ?? 15;
        $page = $params['page'] ?? 1;

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'from' => $paginated->firstItem(),
            'to' => $paginated->lastItem(),
            'links' => [
                'first' => $paginated->url(1),
                'last' => $paginated->url($paginated->lastPage()),
                'prev' => $paginated->previousPageUrl(),
                'next' => $paginated->nextPageUrl(),
            ]
        ];
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
