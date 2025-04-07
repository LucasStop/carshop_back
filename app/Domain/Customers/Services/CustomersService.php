<?php

namespace App\Domain\Customers\Services;

use App\Domain\Customers\Entities\Customers;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use Illuminate\Support\Facades\Log;

class CustomersService
{
    public function __construct(
        private Customers $entity
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->all();
    }

    public function find($id): ?Customers
    {
        return $this->entity->find($id);
    }

    public function create(array $data): Customers
    {
        try {
            return $this->entity->create($data);
        } catch (Exception $e) {
            Log::error('Erro ao criar cliente: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, array $data): bool
    {
        try {
            $customer = $this->entity->find($id);
            
            if (!$customer) {
                throw new Exception("Cliente com ID {$id} não encontrado");
            }
            
            return $customer->update($data);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar cliente: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id): bool
    {
        try {
            $customer = $this->entity->find($id);
            
            if (!$customer) {
                throw new Exception("Cliente com ID {$id} não encontrado");
            }
            
            return $customer->delete();
        } catch (Exception $e) {
            Log::error('Erro ao excluir cliente: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findByCpf($cpf): ?Customers
    {
        return $this->entity->where('cpf', $cpf)->first();
    }
    
    /**
     * Busca os clientes mais frequentes (com mais compras)
     * 
     * @param int $limit
     * @return Collection
     */
    public function findTopCustomers(int $limit = 5): Collection
    {
        try {
            return $this->entity
                ->withCount('sales')
                ->orderBy('sales_count', 'desc')
                ->limit($limit)
                ->get();
        } catch (Exception $e) {
            Log::error('Erro ao buscar melhores clientes: ' . $e->getMessage());
            throw $e;
        }
    }
}
