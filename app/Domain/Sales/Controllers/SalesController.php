<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Sales\Services\SalesService;
use App\Domain\Cars\Services\CarsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function __construct(
        private SalesService $service,
        private CarsService $carsService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $sales = $this->service->all();
            
            // Carrega os relacionamentos
            $sales->load(['car', 'car.model', 'car.model.brand', 'customer', 'employee']);
            
            return response()->json($sales, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar vendas: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar vendas',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'car_id' => 'required|exists:cars,id',
                'customer_user_id' => 'required|exists:users,id',
                'employee_user_id' => 'required|exists:users,id',
                'sale_date' => 'required|date',
                'final_price' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            // Verificar se o carro já foi vendido
            $car = $this->carsService->find($validated['car_id']);
            if ($car->status === 'sold') {
                DB::rollBack();
                return response()->json([
                    'message' => 'Este carro já foi vendido'
                ], Response::HTTP_CONFLICT);
            }

            // Criar a venda (também atualiza o status do carro para 'sold')
            $sale = $this->service->create($validated);
            
            // Carrega os relacionamentos
            $sale->load(['car', 'car.model', 'car.model.brand', 'customer', 'employee']);
            
            DB::commit();
            return response()->json($sale, Response::HTTP_CREATED);
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
                'message' => 'Erro ao cadastrar venda',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar venda: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar venda',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $sale = $this->service->find($id);

            if (!$sale) {
                return response()->json([
                    'message' => 'Venda não encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            // Carrega os relacionamentos
            $sale->load(['car', 'car.model', 'car.model.brand', 'customer', 'employee']);

            return response()->json($sale, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar venda: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar venda',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $sale = $this->service->find($id);

            if (!$sale) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Venda não encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            $validated = $request->validate([
                'car_id' => 'sometimes|required|exists:cars,id',
                'customer_user_id' => 'sometimes|required|exists:users,id',
                'employee_user_id' => 'sometimes|required|exists:users,id',
                'sale_date' => 'sometimes|required|date',
                'final_price' => 'sometimes|required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            // Se o carro estiver sendo alterado, verificar disponibilidade
            if (isset($validated['car_id']) && $validated['car_id'] != $sale->car_id) {
                // Restaurar o status do carro antigo
                $this->carsService->update($sale->car_id, ['status' => 'available']);
                
                // Verificar se o novo carro já foi vendido
                $newCar = $this->carsService->find($validated['car_id']);
                if ($newCar->status === 'sold') {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'O novo carro selecionado já foi vendido'
                    ], Response::HTTP_CONFLICT);
                }
            }

            // Atualizar a venda
            $this->service->update($id, $validated);

            $updatedSale = $this->service->find($id);
            $updatedSale->load(['car', 'car.model', 'car.model.brand', 'customer', 'employee']);
            
            DB::commit();
            return response()->json($updatedSale, Response::HTTP_OK);
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
                'message' => 'Erro ao atualizar venda',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar venda: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar venda',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $sale = $this->service->find($id);

            if (!$sale) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Venda não encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            // Excluir a venda (também atualiza o status do carro para 'available')
            $this->service->delete($id);

            DB::commit();
            return response()->json([
                'message' => 'Venda excluída com sucesso'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir venda: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao excluir venda',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function byCustomer(int $customerId): JsonResponse
    {
        try {
            $sales = $this->service->findByCustomer($customerId);
            
            // Carrega os relacionamentos
            $sales->load(['car', 'car.model', 'car.model.brand', 'customer', 'employee']);
            
            return response()->json($sales, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar vendas por cliente: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar vendas por cliente',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function byEmployee(int $employeeId): JsonResponse
    {
        try {
            $sales = $this->service->findByEmployee($employeeId);
            
            // Carrega os relacionamentos
            $sales->load(['car', 'car.model', 'car.model.brand', 'customer', 'employee']);
            
            return response()->json($sales, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar vendas por funcionário: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar vendas por funcionário',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function byCar(int $carId): JsonResponse
    {
        try {
            $sale = $this->service->findByCar($carId);

            if (!$sale) {
                return response()->json([
                    'message' => 'Nenhuma venda encontrada para este carro'
                ], Response::HTTP_NOT_FOUND);
            }

            // Carrega os relacionamentos
            $sale->load(['car', 'car.model', 'car.model.brand', 'customer', 'employee']);
            
            return response()->json($sale, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar venda por carro: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar venda por carro',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Gera um resumo de vendas por período
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);
            
            $summary = $this->service->getSalesSummary(
                $validated['start_date'],
                $validated['end_date']
            );
            
            return response()->json($summary, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            Log::error('Erro ao gerar resumo de vendas: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao gerar resumo de vendas',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
