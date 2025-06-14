<?php

namespace App\Domain\Models\Controllers;

use App\Domain\Models\Services\ModelsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;

class ModelsController extends Controller
{
    public function __construct(
        private ModelsService $service
    ) {}

    public function index(): JsonResponse
    {
        try {
            $models = $this->service->all();
            
            // Carrega o relacionamento com a marca
            $models->load('brand');
            
            return response()->json($models, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar modelos: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar modelos',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {            $validated = $request->validate([
                'brand_id' => 'required|exists:brands,id',
                'name' => 'required|string|max:50',
                'year_model' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
                'engine' => 'nullable|string|max:50',
                'power' => 'nullable|integer|min:0',
                'quantity' => 'nullable|integer|min:0',
            ]);

            $model = $this->service->create($validated);
            
            // Carrega o relacionamento com a marca
            $model->load('brand');
            
            return response()->json($model, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar modelo',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao cadastrar modelo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar modelo',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $model = $this->service->find($id);

            if (!$model) {
                return response()->json([
                    'message' => 'Modelo não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Carrega o relacionamento com a marca
            $model->load('brand');

            return response()->json($model, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar modelo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar modelo',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $model = $this->service->find($id);

            if (!$model) {
                return response()->json([
                    'message' => 'Modelo não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }            $validated = $request->validate([
                'brand_id' => 'sometimes|required|exists:brands,id',
                'name' => 'sometimes|required|string|max:50',
                'year_model' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
                'engine' => 'nullable|string|max:50',
                'power' => 'nullable|integer|min:0',
                'quantity' => 'nullable|integer|min:0',
            ]);

            $this->service->update($id, $validated);

            $updatedModel = $this->service->find($id);
            $updatedModel->load('brand');
            
            return response()->json($updatedModel, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar modelo',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar modelo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar modelo',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $model = $this->service->find($id);

            if (!$model) {
                return response()->json([
                    'message' => 'Modelo não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar se o modelo possui carros associados
            if ($model->cars()->count() > 0) {
                return response()->json([
                    'message' => 'Não é possível excluir este modelo pois existem carros associados a ele'
                ], Response::HTTP_CONFLICT);
            }

            $this->service->delete($id);

            return response()->json([
                'message' => 'Modelo excluído com sucesso'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao excluir modelo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao excluir modelo',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function byBrand(int $brandId): JsonResponse
    {
        try {
            $models = $this->service->findByBrand($brandId);
            
            // Carrega o relacionamento com a marca
            $models->load('brand');
            
            return response()->json($models, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar modelos por marca: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar modelos por marca',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Atualiza a quantidade de um modelo
     */
    public function updateQuantity(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:0',
            ]);
            
            $model = $this->service->find($id);
            
            if (!$model) {
                return response()->json([
                    'message' => 'Modelo não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }
            
            $this->service->updateQuantity($id, $validated['quantity']);
            
            $updatedModel = $this->service->find($id);
            $updatedModel->load('brand');
            
            return response()->json($updatedModel, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar quantidade: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar quantidade',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Incrementa a quantidade de um modelo
     */
    public function incrementQuantity(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'nullable|integer|min:1',
            ]);
            
            $model = $this->service->find($id);
            
            if (!$model) {
                return response()->json([
                    'message' => 'Modelo não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }
            
            $amount = $validated['amount'] ?? 1;
            $this->service->incrementQuantity($id, $amount);
            
            $updatedModel = $this->service->find($id);
            $updatedModel->load('brand');
            
            return response()->json($updatedModel, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            Log::error('Erro ao incrementar quantidade: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao incrementar quantidade',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Decrementa a quantidade de um modelo
     */
    public function decrementQuantity(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'nullable|integer|min:1',
            ]);
            
            $model = $this->service->find($id);
            
            if (!$model) {
                return response()->json([
                    'message' => 'Modelo não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }
            
            $amount = $validated['amount'] ?? 1;
            
            // Verificar se há estoque suficiente
            if ($model->quantity < $amount) {
                return response()->json([
                    'message' => 'Estoque insuficiente para este modelo'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $this->service->decrementQuantity($id, $amount);
            
            $updatedModel = $this->service->find($id);
            $updatedModel->load('brand');
            
            return response()->json($updatedModel, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            Log::error('Erro ao decrementar quantidade: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao decrementar quantidade',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Busca modelos com estoque baixo
     */
    public function lowStock(Request $request): JsonResponse
    {
        try {
            $minQuantity = $request->get('min_quantity', 5);
            
            if (!is_numeric($minQuantity) || $minQuantity < 0) {
                $minQuantity = 5;
            }
            
            $models = $this->service->findLowStock((int)$minQuantity);
            $models->load('brand');
            
            return response()->json($models, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar modelos com estoque baixo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar modelos com estoque baixo',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
