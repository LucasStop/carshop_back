<?php

namespace App\Domain\Employees\Controllers;

use App\Domain\Employees\Services\EmployeesService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;

class EmployeesController extends Controller
{
    public function __construct(
        private EmployeesService $service
    ) {}

    public function index(): JsonResponse
    {
        try {
            $employees = $this->service->all();
            return response()->json($employees, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar funcionários: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar funcionários',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'position' => 'nullable|string|max:50',
                'email' => 'nullable|email|unique:employees,email|max:100',
                'phone' => 'nullable|string|max:20',
                'cpf' => 'nullable|string|max:14|unique:employees,cpf|regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                'rg' => 'nullable|string|max:20|unique:employees,rg',
                'birth_date' => 'nullable|date|before:-18 years',
                'address' => 'nullable|string|max:200',
                'number' => 'nullable|string|max:10',
                'complement' => 'nullable|string|max:50',
                'city' => 'nullable|string|max:50',
                'state' => 'nullable|string|max:2',
                'zip_code' => 'nullable|string|max:10|regex:/^\d{5}\-\d{3}$/',
                'hire_date' => 'nullable|date|before_or_equal:today',
                'salary' => 'nullable|numeric|min:0',
            ]);

            $employee = $this->service->create($validated);
            return response()->json($employee, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar funcionário',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao cadastrar funcionário: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar funcionário',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $employee = $this->service->find($id);

            if (!$employee) {
                return response()->json([
                    'message' => 'Funcionário não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json($employee, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar funcionário: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar funcionário',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $employee = $this->service->find($id);

            if (!$employee) {
                return response()->json([
                    'message' => 'Funcionário não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:100',
                'position' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:100|unique:employees,email,'.$id,
                'phone' => 'nullable|string|max:20',
                'cpf' => 'nullable|string|max:14|unique:employees,cpf,'.$id.'|regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                'rg' => 'nullable|string|max:20|unique:employees,rg,'.$id,
                'birth_date' => 'nullable|date|before:-18 years',
                'address' => 'nullable|string|max:200',
                'number' => 'nullable|string|max:10',
                'complement' => 'nullable|string|max:50',
                'city' => 'nullable|string|max:50',
                'state' => 'nullable|string|max:2',
                'zip_code' => 'nullable|string|max:10|regex:/^\d{5}\-\d{3}$/',
                'hire_date' => 'nullable|date|before_or_equal:today',
                'salary' => 'nullable|numeric|min:0',
            ]);

            $this->service->update($id, $validated);

            return response()->json($this->service->find($id), Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar funcionário',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar funcionário: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar funcionário',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $employee = $this->service->find($id);

            if (!$employee) {
                return response()->json([
                    'message' => 'Funcionário não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar se o funcionário possui vendas associadas
            if ($employee->sales()->count() > 0) {
                return response()->json([
                    'message' => 'Não é possível excluir este funcionário pois existem vendas associadas a ele'
                ], Response::HTTP_CONFLICT);
            }

            $this->service->delete($id);

            return response()->json([
                'message' => 'Funcionário excluído com sucesso'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao excluir funcionário: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao excluir funcionário',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findByCpf(string $cpf): JsonResponse
    {
        try {
            // Validar formato do CPF
            if (!preg_match('/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/', $cpf)) {
                return response()->json([
                    'message' => 'Formato de CPF inválido. Use o formato 000.000.000-00'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $employee = $this->service->findByCpf($cpf);

            if (!$employee) {
                return response()->json([
                    'message' => 'Funcionário não encontrado com este CPF'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json($employee, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar funcionário por CPF: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar funcionário por CPF',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findByPosition(string $position): JsonResponse
    {
        try {
            $employees = $this->service->findByPosition($position);
            
            if ($employees->isEmpty()) {
                return response()->json([
                    'message' => 'Nenhum funcionário encontrado com este cargo'
                ], Response::HTTP_NOT_FOUND);
            }
            
            return response()->json($employees, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar funcionários por cargo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar funcionários por cargo',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Busca os melhores vendedores
     */
    public function topSellers(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 5);
            
            // Validar limite
            if (!is_numeric($limit) || $limit < 1) {
                $limit = 5;
            }
            
            $topSellers = $this->service->findTopSellers((int)$limit);
            
            return response()->json($topSellers, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar top vendedores: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar top vendedores',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
