<?php

namespace App\Domain\Addresses\Services;

use App\Domain\Addresses\Entities\Addresses;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use Illuminate\Support\Facades\Log;

class AddressesService
{
    public function __construct(
        private Addresses $entity
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->all();
    }

    public function find($id): ?Addresses
    {
        return $this->entity->find($id);
    }

    public function create(array $data): Addresses
    {
        try {
            return $this->entity->create($data);
        } catch (Exception $e) {
            Log::error('Erro ao criar endereço: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, array $data): bool
    {
        try {
            $address = $this->entity->find($id);
            
            if (!$address) {
                throw new Exception("Endereço com ID {$id} não encontrado");
            }
            
            return $address->update($data);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar endereço: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id): bool
    {
        try {
            $address = $this->entity->find($id);
            
            if (!$address) {
                throw new Exception("Endereço com ID {$id} não encontrado");
            }
            
            return $address->delete();
        } catch (Exception $e) {
            Log::error('Erro ao excluir endereço: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca endereço pelo ID do usuário
     * 
     * @param int $userId
     * @return Addresses|null
     */
    public function findByUser(int $userId): ?Addresses
    {
        return $this->entity->where('user_id', $userId)->first();
    }
}