<?php

namespace App\Domain\Users\Services;

use App\Domain\Users\Entities\Users;
use App\Domain\Addresses\Services\AddressesService;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UsersService
{
    public function __construct(
        private Users $entity,
        private AddressesService $addressesService
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->with(['role', 'address'])->get();
    }

    public function find($id): ?Users
    {
        return $this->entity->with(['role', 'address'])->find($id);
    }

    public function create(array $data): Users
    {
        DB::beginTransaction();
        try {
            // Separar dados do endereço, se existirem
            $addressData = null;
            if (isset($data['address'])) {
                $addressData = $data['address'];
                unset($data['address']);
            }
            
            // Criar o usuário
            $user = $this->entity->create($data);
            
            // Se houver dados de endereço, criar o endereço vinculado ao usuário
            if ($addressData && is_array($addressData)) {
                $addressData['user_id'] = $user->id;
                $this->addressesService->create($addressData);
            }

            // Recarregar o usuário com o relacionamento de endereço
            $user->load(['role', 'address']);
            
            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar usuário: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, array $data): bool
    {
        DB::beginTransaction();
        try {
            $user = $this->entity->find($id);
            
            if (!$user) {
                throw new Exception("Usuário com ID {$id} não encontrado");
            }
            
            // Separar dados do endereço, se existirem
            $addressData = null;
            if (isset($data['address'])) {
                $addressData = $data['address'];
                unset($data['address']);
            }
            
            // Atualizar o usuário
            $result = $user->update($data);
            
            // Se houver dados de endereço, atualizar ou criar o endereço
            if ($addressData && is_array($addressData)) {
                $address = $this->addressesService->findByUser($id);
                
                if ($address) {
                    // Se já existe um endereço, atualize-o
                    $this->addressesService->update($address->id, $addressData);
                } else {
                    // Se não existe, crie um novo
                    $addressData['user_id'] = $id;
                    $this->addressesService->create($addressData);
                }
            }
            
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id): bool
    {
        try {
            $user = $this->entity->find($id);
            
            if (!$user) {
                throw new Exception("Usuário com ID {$id} não encontrado");
            }
            
            return $user->delete();
        } catch (Exception $e) {
            Log::error('Erro ao excluir usuário: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca usuários por role (função)
     * 
     * @param int $roleId
     * @return Collection
     */
    public function findByRole(int $roleId): Collection
    {
        return $this->entity->with(['role', 'address'])
            ->where('role_id', $roleId)
            ->get();
    }
}