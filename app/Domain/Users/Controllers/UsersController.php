<?php

namespace App\Domain\Users\Controllers;

use App\Domain\Users\Services\UsersService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    public function __construct(
        private UsersService $service
    ) {}

    public function index(): JsonResponse
    {
        try {
            $users = $this->service->all();
            return response()->json($users, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar usuários: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar usuários',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'role_id' => 'required|exists:roles,id',
                'name' => 'required|string|max:100',
                'email' => 'nullable|email|max:100|unique:users,email',
                'password' => 'nullable|string|min:6',
                'phone' => 'nullable|string|max:20',
                'cpf' => 'nullable|string|max:14|unique:users,cpf',
                'rg' => 'nullable|string|max:20|unique:users,rg',
                'birth_date' => 'nullable|date',
                // Validação para os campos de endereço
                'address' => 'nullable|array',
                'address.address' => 'nullable|string|max:200',
                'address.number' => 'nullable|string|max:10',
                'address.complement' => 'nullable|string|max:50',
                'address.city' => 'nullable|string|max:50',
                'address.state' => 'nullable|string|max:2',
                'address.zip_code' => 'nullable|string|max:10',
            ]);

            $user = $this->service->create($validated);
            
            // Carrega os relacionamentos
            $user->load(['role', 'address']);
            
            return response()->json($user, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar usuário',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao cadastrar usuário: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar usuário',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->service->find($id);
            
            if (!$user) {
                return response()->json([
                    'message' => 'Usuário não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }
            
            return response()->json($user, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar usuário: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar usuário',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->service->find($id);
            
            if (!$user) {
                return response()->json([
                    'message' => 'Usuário não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $validated = $request->validate([
                'role_id' => 'sometimes|required|exists:roles,id',
                'name' => 'sometimes|required|string|max:100',
                'email' => 'sometimes|nullable|email|max:100|unique:users,email,' . $id,
                'password' => 'sometimes|nullable|string|min:6',
                'phone' => 'sometimes|nullable|string|max:20',
                'cpf' => 'sometimes|nullable|string|max:14|unique:users,cpf,' . $id,
                'rg' => 'sometimes|nullable|string|max:20|unique:users,rg,' . $id,
                'birth_date' => 'sometimes|nullable|date',
                // Validação para os campos de endereço
                'address' => 'sometimes|nullable|array',
                'address.address' => 'nullable|string|max:200',
                'address.number' => 'nullable|string|max:10',
                'address.complement' => 'nullable|string|max:50',
                'address.city' => 'nullable|string|max:50',
                'address.state' => 'nullable|string|max:2',
                'address.zip_code' => 'nullable|string|max:10',
            ]);

            $this->service->update($id, $validated);

            $updatedUser = $this->service->find($id);
            return response()->json($updatedUser, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar usuário',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar usuário',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $user = $this->service->find($id);
            
            if (!$user) {
                return response()->json([
                    'message' => 'Usuário não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->service->delete($id);
            
            return response()->json([
                'message' => 'Usuário excluído com sucesso'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao excluir usuário: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao excluir usuário',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Busca usuários por função
     */
    public function byRole(int $roleId): JsonResponse
    {
        try {
            $users = $this->service->findByRole($roleId);
            
            return response()->json($users, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar usuários por função: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar usuários por função',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}