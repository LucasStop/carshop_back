<?php

namespace App\Domain\Customers\Controllers;

use App\Domain\Customers\Services\CustomersService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;

class CustomersController extends Controller
{
    public function __construct(
        private CustomersService $service
    ) {}

    public function index(): JsonResponse
    {
        try {
            $customers = $this->service->all();
            return response()->json($customers, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar clientes: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar clientes',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'nullable|email|unique:customers,email|max:100',
                'phone' => 'nullable|string|max:20',
                'cpf' => 'nullable|string|max:14|unique:customers,cpf|regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                'rg' => 'nullable|string|max:20|unique:customers,rg',
                'birth_date' => 'nullable|date|before:today',
                'address' => 'nullable|string|max:200',
                'number' => 'nullable|string|max:10',
                'complement' => 'nullable|string|max:50',
                'city' => 'nullable|string|max:50',
                'state' => 'nullable|string|max:2',
                'zip_code' => 'nullable|string|max:10|regex:/^\d{5}\-\d{3}$/',
            ]);

            $customer = $this->service->create($validated);
            return response()->json($customer, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar cliente',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao cadastrar cliente: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar cliente',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $customer = $this->service->find($id);

            if (!$customer) {
                return response()->json([
                    'message' => 'Cliente não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json($customer, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar cliente: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar cliente',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $customer = $this->service->find($id);

            if (!$customer) {
                return response()->json([
                    'message' => 'Cliente não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:100',
                'email' => 'nullable|email|max:100|unique:customers,email,'.$id,
                'phone' => 'nullable|string|max:20',
                'cpf' => 'nullable|string|max:14|unique:customers,cpf,'.$id.'|regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                'rg' => 'nullable|string|max:20|unique:customers,rg,'.$id,
                'birth_date' => 'nullable|date|before:today',
                'address' => 'nullable|string|max:200',
                'number' => 'nullable|string|max:10',
                'complement' => 'nullable|string|max:50',
                'city' => 'nullable|string|max:50',
                'state' => 'nullable|string|max:2',
                'zip_code' => 'nullable|string|max:10|regex:/^\d{5}\-\d{3}$/',
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
                'message' => 'Erro ao atualizar cliente',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar cliente: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar cliente',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $customer = $this->service->find($id);

            if (!$customer) {
                return response()->json([
                    'message' => 'Cliente não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar se o cliente possui vendas associadas
            if ($customer->sales()->count() > 0) {
                return response()->json([
                    'message' => 'Não é possível excluir este cliente pois existem vendas associadas a ele'
                ], Response::HTTP_CONFLICT);
            }

            $this->service->delete($id);

            return response()->json([
                'message' => 'Cliente excluído com sucesso'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao excluir cliente: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao excluir cliente',
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
            
            $customer = $this->service->findByCpf($cpf);

            if (!$customer) {
                return response()->json([
                    'message' => 'Cliente não encontrado com este CPF'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json($customer, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar cliente por CPF: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar cliente por CPF',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Busca os melhores clientes (com mais compras)
     */
    public function topCustomers(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 5);
            
            // Validar limite
            if (!is_numeric($limit) || $limit < 1) {
                $limit = 5;
            }
            
            $topCustomers = $this->service->findTopCustomers((int)$limit);
            
            return response()->json($topCustomers, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar melhores clientes: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar melhores clientes',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
