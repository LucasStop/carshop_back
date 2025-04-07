<?php

namespace App\Domain\Employees\Services;

use App\Domain\Employees\Entities\Employees;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use Illuminate\Support\Facades\Log;

class EmployeesService
{
    public function __construct(
        private Employees $entity
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->all();
    }

    public function find($id): ?Employees
    {
        return $this->entity->find($id);
    }

    public function create(array $data): Employees
    {
        try {
            return $this->entity->create($data);
        } catch (Exception $e) {
            Log::error('Erro ao criar funcionário: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, array $data): bool
    {
        try {
            $employee = $this->entity->find($id);
            
            if (!$employee) {
                throw new Exception("Funcionário com ID {$id} não encontrado");
            }
            
            return $employee->update($data);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar funcionário: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id): bool
    {
        try {
            $employee = $this->entity->find($id);
            
            if (!$employee) {
                throw new Exception("Funcionário com ID {$id} não encontrado");
            }
            
            return $employee->delete();
        } catch (Exception $e) {
            Log::error('Erro ao excluir funcionário: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findByCpf($cpf): ?Employees
    {
        return $this->entity->where('cpf', $cpf)->first();
    }

    public function findByPosition($position): Collection
    {
        return $this->entity->where('position', $position)->get();
    }
    
    /**
     * Busca os top vendedores baseado na quantidade de vendas
     * 
     * @param int $limit
     * @return Collection
     */
    public function findTopSellers(int $limit = 5): Collection
    {
        try {
            return $this->entity
                ->withCount('sales')
                ->orderBy('sales_count', 'desc')
                ->limit($limit)
                ->get();
        } catch (Exception $e) {
            Log::error('Erro ao buscar top vendedores: ' . $e->getMessage());
            throw $e;
        }
    }
}
