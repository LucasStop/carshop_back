<?php

namespace App\Domain\Cars\Controllers;

use App\Domain\Cars\Services\CarsService;
use App\Domain\Models\Services\ModelsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CarsController extends Controller
{
    public function __construct(
        private CarsService $service,
        private ModelsService $modelService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $cars = $this->service->all();
            
            // Carrega os relacionamentos
            $cars->load(['model', 'model.brand']);
            
            return response()->json($cars, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar carros: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar carros',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'model_id' => 'required|exists:models,id',
                'vin' => 'required|string|max:50|unique:cars,vin',
                'color' => 'nullable|string|max:30',
                'manufacture_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'mileage' => 'nullable|integer|min:0',
                'status' => 'nullable|in:available,sold,reserved,maintenance',
                'inclusion_date' => 'nullable|date',
            ]);

            // Verificar estoque do modelo
            $model = $this->modelService->find($validated['model_id']);
            if (!$model) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Modelo não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }
            
            if ($model->quantity <= 0) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Estoque insuficiente para este modelo'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Criar o carro
            $car = $this->service->create($validated);
            
            // Decrementar o estoque do modelo
            $this->modelService->decrementQuantity($validated['model_id']);
            
            // Carrega os relacionamentos
            $car->load(['model', 'model.brand']);
            
            DB::commit();
            return response()->json($car, Response::HTTP_CREATED);
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
                'message' => 'Erro ao cadastrar carro',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar carro: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar carro',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $car = $this->service->find($id);

            if (!$car) {
                return response()->json([
                    'message' => 'Carro não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Carrega os relacionamentos
            $car->load(['model', 'model.brand']);

            return response()->json($car, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar carro: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar carro',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $car = $this->service->find($id);

            if (!$car) {
                return response()->json([
                    'message' => 'Carro não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar se o carro está vendido
            if ($car->status === 'sold' && !$request->has('status')) {
                return response()->json([
                    'message' => 'Não é possível atualizar um carro que já foi vendido'
                ], Response::HTTP_CONFLICT);
            }

            $validated = $request->validate([
                'model_id' => 'sometimes|required|exists:models,id',
                'vin' => 'sometimes|required|string|max:50|unique:cars,vin,'.$id,
                'color' => 'nullable|string|max:30',
                'manufacture_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'mileage' => 'nullable|integer|min:0',
                'status' => 'nullable|in:available,sold,reserved,maintenance',
                'inclusion_date' => 'nullable|date',
            ]);

            $this->service->update($id, $validated);

            $updatedCar = $this->service->find($id);
            $updatedCar->load(['model', 'model.brand']);
            
            return response()->json($updatedCar, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar carro',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar carro: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar carro',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $car = $this->service->find($id);

            if (!$car) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Carro não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar se o carro está vendido
            if ($car->status === 'sold') {
                DB::rollBack();
                return response()->json([
                    'message' => 'Não é possível excluir um carro que já foi vendido'
                ], Response::HTTP_CONFLICT);
            }

            // Verificar se o carro possui vendas associadas
            if ($car->sales()->count() > 0) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Não é possível excluir este carro pois existem vendas associadas a ele'
                ], Response::HTTP_CONFLICT);
            }

            // Armazenar o model_id para incrementar o estoque após a exclusão
            $modelId = $car->model_id;
            
            // Excluir o carro
            $this->service->delete($id);
            
            // Incrementar o estoque do modelo
            $this->modelService->incrementQuantity($modelId);

            DB::commit();
            return response()->json([
                'message' => 'Carro excluído com sucesso'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir carro: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao excluir carro',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function byModel(int $modelId): JsonResponse
    {
        try {
            $cars = $this->service->findByModel($modelId);
            
            // Carrega os relacionamentos
            $cars->load(['model', 'model.brand']);
            
            return response()->json($cars, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar carros por modelo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar carros por modelo',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function byStatus(string $status): JsonResponse
    {
        try {
            // Validar status
            if (!in_array($status, ['available', 'sold', 'reserved', 'maintenance'])) {
                return response()->json([
                    'message' => 'Status inválido'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $cars = $this->service->findByStatus($status);
            
            // Carrega os relacionamentos
            $cars->load(['model', 'model.brand']);
            
            return response()->json($cars, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar carros por status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar carros por status',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Busca carros por faixa de preço
     */
    public function byPriceRange(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'min_price' => 'required|numeric|min:0',
                'max_price' => 'required|numeric|gt:min_price',
            ]);
            
            $cars = $this->service->findByPriceRange(
                $validated['min_price'],
                $validated['max_price']
            );
            
            return response()->json($cars, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            Log::error('Erro ao buscar carros por faixa de preço: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar carros por faixa de preço',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
