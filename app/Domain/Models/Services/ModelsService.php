<?php

namespace App\Domain\Models\Services;

use App\Domain\Models\Entities\Models;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use Illuminate\Support\Facades\Log;

class ModelsService
{
    public function __construct(
        private Models $entity
    ) {}

    public function all(array $params = []): array
    {
        $query = $this->entity->with(['brand']);

        // Aplicar filtro de busca por nome do modelo ou marca
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('engine', 'LIKE', "%{$search}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($search) {
                        $brandQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Aplicar filtro por marca (aceita nome ou ID)
        if (!empty($params['brand'])) {
            $brand = $params['brand'];
            if (is_numeric($brand)) {
                $query->where('brand_id', $brand);
            } else {
                $query->whereHas('brand', function ($q) use ($brand) {
                    $q->where('name', 'LIKE', "%{$brand}%");
                });
            }
        }

        // Aplicar filtro específico por brand_id
        if (!empty($params['brand_id'])) {
            $query->where('brand_id', $params['brand_id']);
        }

        // Aplicar filtro por ano do modelo
        if (!empty($params['year_model'])) {
            $query->where('year_model', $params['year_model']);
        }

        // Aplicar filtro por motor
        if (!empty($params['engine'])) {
            $query->where('engine', 'LIKE', "%{$params['engine']}%");
        }

        // Aplicar filtro por potência
        if (!empty($params['min_power'])) {
            $query->where('power', '>=', $params['min_power']);
        }
        if (!empty($params['max_power'])) {
            $query->where('power', '<=', $params['max_power']);
        }

        // Aplicar filtro por quantidade em estoque
        if (!empty($params['min_quantity'])) {
            $query->where('quantity', '>=', $params['min_quantity']);
        }
        if (!empty($params['max_quantity'])) {
            $query->where('quantity', '<=', $params['max_quantity']);
        }

        // Filtro para estoque baixo
        if (!empty($params['low_stock'])) {
            $lowStockLimit = is_numeric($params['low_stock']) ? $params['low_stock'] : 5;
            $query->where('quantity', '<', $lowStockLimit);
        }

        // Aplicar ordenação
        $orderBy = $params['order_by'] ?? 'id';
        $orderDirection = $params['order_direction'] ?? 'desc';

        // Validar campos de ordenação
        $allowedOrderFields = ['id', 'name', 'year_model', 'engine', 'power', 'quantity', 'created_at', 'updated_at'];
        if (in_array($orderBy, $allowedOrderFields)) {
            $query->orderBy($orderBy, $orderDirection);
        } else {
            $query->orderBy('id', 'desc');
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


    public function find($id): ?Models
    {
        return $this->entity->find($id);
    }

    public function create(array $data): Models
    {
        try {
            return $this->entity->create($data);
        } catch (Exception $e) {
            Log::error('Erro ao criar modelo: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, array $data): bool
    {
        try {
            $model = $this->entity->find($id);

            if (!$model) {
                throw new Exception("Modelo com ID {$id} não encontrado");
            }

            return $model->update($data);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar modelo: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id): bool
    {
        try {
            $model = $this->entity->find($id);

            if (!$model) {
                throw new Exception("Modelo com ID {$id} não encontrado");
            }

            return $model->delete();
        } catch (Exception $e) {
            Log::error('Erro ao excluir modelo: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findByBrand($brandId): Collection
    {
        return $this->entity->where('brand_id', $brandId)->get();
    }

    /**
     * Ajusta a quantidade do modelo
     *
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public function updateQuantity(int $id, int $quantity): bool
    {
        try {
            $model = $this->entity->find($id);

            if (!$model) {
                throw new Exception("Modelo com ID {$id} não encontrado");
            }

            return $model->update(['quantity' => $quantity]);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar quantidade do modelo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Incrementa a quantidade de um modelo
     *
     * @param int $id
     * @param int $amount
     * @return bool
     */
    public function incrementQuantity(int $id, int $amount = 1): bool
    {
        try {
            $model = $this->entity->find($id);

            if (!$model) {
                throw new Exception("Modelo com ID {$id} não encontrado");
            }

            return $model->increment('quantity', $amount);
        } catch (Exception $e) {
            Log::error('Erro ao incrementar quantidade do modelo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Decrementa a quantidade de um modelo
     *
     * @param int $id
     * @param int $amount
     * @return bool
     */
    public function decrementQuantity(int $id, int $amount = 1): bool
    {
        try {
            $model = $this->entity->find($id);

            if (!$model) {
                throw new Exception("Modelo com ID {$id} não encontrado");
            }

            // Verificar se há estoque suficiente
            if ($model->quantity < $amount) {
                throw new Exception("Estoque insuficiente para o modelo com ID {$id}");
            }

            return $model->decrement('quantity', $amount);
        } catch (Exception $e) {
            Log::error('Erro ao decrementar quantidade do modelo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca modelos com quantidade abaixo do limite mínimo
     *
     * @param int $minQuantity
     * @return Collection
     */
    public function findLowStock(int $minQuantity = 5): Collection
    {
        try {
            return $this->entity
                ->where('quantity', '<', $minQuantity)
                ->with('brand')
                ->get();
        } catch (Exception $e) {
            Log::error('Erro ao buscar modelos com estoque baixo: ' . $e->getMessage());
            throw $e;
        }
    }
}
