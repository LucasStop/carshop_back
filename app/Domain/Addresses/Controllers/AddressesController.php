<?php

namespace App\Domain\Addresses\Controllers;

use App\Domain\Addresses\Services\AddressesService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AddressesController extends Controller
{
    public function __construct(
        private AddressesService $service
    ) {}

    public function index(): JsonResponse
    {
        try {
            $addresses = $this->service->all();
            
            // Carrega os relacionamentos
            $addresses->load(['user']);
            
            return response()->json($addresses, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar endereços: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar endereços',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'address' => 'required|string|max:200',
                'number' => 'nullable|string|max:10',
                'complement' => 'nullable|string|max:50',
                'city' => 'required|string|max:50',
                'state' => 'required|string|max:2',
                'zip_code' => 'required|string|max:10',
            ]);

            // Criar o endereço
            $address = $this->service->create($validated);
            
            // Carrega os relacionamentos
            $address->load(['user']);
            
            DB::commit();
            return response()->json($address, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar endereço',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar endereço: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar endereço',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $address = $this->service->find($id);

            if (!$address) {
                return response()->json([
                    'message' => 'Endereço não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Carrega os relacionamentos
            $address->load(['user']);

            return response()->json($address, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar endereço: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar endereço',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $address = $this->service->find($id);

            if (!$address) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Endereço não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'address' => 'sometimes|required|string|max:200',
                'number' => 'nullable|string|max:10',
                'complement' => 'nullable|string|max:50',
                'city' => 'sometimes|required|string|max:50',
                'state' => 'sometimes|required|string|max:2',
                'zip_code' => 'sometimes|required|string|max:10',
            ]);

            // Atualizar o endereço
            $this->service->update($id, $validated);

            $updatedAddress = $this->service->find($id);
            $updatedAddress->load(['user']);
            
            DB::commit();
            return response()->json($updatedAddress, Response::HTTP_OK);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar endereço',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar endereço: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar endereço',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $address = $this->service->find($id);

            if (!$address) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Endereço não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Excluir o endereço
            $this->service->delete($id);

            DB::commit();
            return response()->json([
                'message' => 'Endereço excluído com sucesso'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir endereço: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao excluir endereço',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function byUser(int $userId): JsonResponse
    {
        try {
            $addresses = $this->service->findByUser($userId);
            
            // Carrega os relacionamentos
            $addresses->load(['user']);
            
            return response()->json($addresses, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar endereços por usuário: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar endereços por usuário',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}