<?php

namespace App\Domain\Brands\Services;

use App\Domain\Brands\Entities\Brands;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use Illuminate\Support\Facades\Log;

class BrandsService
{
    public function __construct(
        private Brands $entity
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->all();
    }

    public function find($id): ?Brands
    {
        return $this->entity->find($id);
    }

    public function create(array $data): Brands
    {
        try {
            return $this->entity->create($data);
        } catch (Exception $e) {
            Log::error('Erro ao criar marca: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, array $data): bool
    {
        try {
            $brand = $this->entity->find($id);
            
            if (!$brand) {
                throw new Exception("Marca com ID {$id} não encontrada");
            }
            
            return $brand->update($data);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar marca: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id): bool
    {
        try {
            $brand = $this->entity->find($id);
            
            if (!$brand) {
                throw new Exception("Marca com ID {$id} não encontrada");
            }
            
            // Verificar se possui modelos antes de excluir
            if ($brand->models()->count() > 0) {
                throw new Exception("Não é possível excluir uma marca que possui modelos associados");
            }
            
            return $brand->delete();
        } catch (Exception $e) {
            Log::error('Erro ao excluir marca: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca as marcas mais populares baseado no número de modelos
     * 
     * @param int $limit
     * @return Collection
     */
    public function findPopularBrands(int $limit = 5): Collection
    {
        try {
            return $this->entity
                ->withCount('models')
                ->orderBy('models_count', 'desc')
                ->limit($limit)
                ->get();
        } catch (Exception $e) {
            Log::error('Erro ao buscar marcas populares: ' . $e->getMessage());
            throw $e;
        }
    }
}
