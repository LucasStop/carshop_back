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
    ) {}

    public function all(array $params = []): array
    {
        $query = $this->entity->with(['user']);

        // Aplicar filtro de busca por endereço, cidade ou nome do usuário
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('address', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhere('zip_code', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Aplicar filtro por usuário específico
        if (!empty($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }

        // Aplicar filtro por cidade
        if (!empty($params['city'])) {
            $query->where('city', 'LIKE', "%{$params['city']}%");
        }

        // Aplicar filtro por estado
        if (!empty($params['state'])) {
            $query->where('state', $params['state']);
        }

        // Se não houver parâmetros de paginação, retornar todos os resultados
        if (empty($params['page']) && empty($params['per_page'])) {
            return [
                'data' => $query->get(),
                'pagination' => null
            ];
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

    public function findByUser(int $userId): Collection
    {
        return $this->entity->with(['user'])->where('user_id', $userId)->get();
    }
}
