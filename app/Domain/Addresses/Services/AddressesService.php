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

    /**
     * Criar um novo endereço.
     *
     * @param array $data
     * @return Addresses
     * @throws Exception
     */
    public function create(array $data): Addresses
    {
        try {
            return Addresses::create($data);
        } catch (Exception $e) {
            Log::error('Erro ao criar endereço: ' . $e->getMessage());
            throw new Exception('Erro ao criar endereço: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar um endereço existente.
     *
     * @param int $id
     * @param array $data
     * @return Addresses
     * @throws Exception
     */
    public function update(int $id, array $data): Addresses
    {
        try {
            $address = Addresses::findOrFail($id);
            $address->update($data);
            return $address;
        } catch (Exception $e) {
            Log::error('Erro ao atualizar endereço: ' . $e->getMessage());
            throw new Exception('Erro ao atualizar endereço: ' . $e->getMessage());
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
     * Encontrar endereço por ID de usuário.
     *
     * @param int $userId
     * @return Addresses|null
     */
    public function findByUserId(int $userId): ?Addresses
    {
        return Addresses::where('user_id', $userId)->first();
    }
}