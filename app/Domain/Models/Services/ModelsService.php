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
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->all();
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
