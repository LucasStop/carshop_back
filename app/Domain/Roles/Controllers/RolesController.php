<?php

namespace App\Domain\Roles\Controllers;

use App\Domain\Roles\Services\RolesService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;

class RolesController extends Controller
{
    public function __construct(
        private RolesService $service
    ) {}

    public function index(): JsonResponse
    {
        try {
            $roles = $this->service->all();
            return response()->json($roles, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar roles: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar roles',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:roles,name',
                'slug' => 'nullable|string|max:100|unique:roles,slug',
            ]);

            $role = $this->service->create($validated);

            return response()->json($role, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar role',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao cadastrar role: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar role',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $role = $this->service->find($id);

            if (!$role) {
                return response()->json([
                    'message' => 'Role não encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json($role, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar role: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar role',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:roles,name,' . $id,
                'description' => 'nullable|string|max:255',
            ]);

            $updated = $this->service->update($id, $validated);

            if (!$updated) {
                return response()->json([
                    'message' => 'Role não encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            $role = $this->service->find($id);
            return response()->json($role, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar role',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar role: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar role',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $deleted = $this->service->delete($id);

            if (!$deleted) {
                return response()->json([
                    'message' => 'Role não encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'message' => 'Role excluída com sucesso'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao excluir role: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao excluir role',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
