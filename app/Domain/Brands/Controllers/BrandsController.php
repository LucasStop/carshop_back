<?php

namespace App\Domain\Brands\Controllers;

use App\Domain\Brands\Services\BrandsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;

class BrandsController extends Controller
{
    public function __construct(
        private BrandsService $service
    ) {}

    public function index(): JsonResponse
    {
        try {
            $brands = $this->service->all();
            return response()->json($brands, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar marcas: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar marcas',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:brands,name',
                'country_origin' => 'required|string|max:255',
            ]);

            $brand = $this->service->create($validated);
            return response()->json($brand, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar marca',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao cadastrar marca: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao cadastrar marca',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $brand = $this->service->find($id);

            if (!$brand) {
                return response()->json([
                    'message' => 'Marca não encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json($brand, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar marca: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar marca',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $brand = $this->service->find($id);

            if (!$brand) {
                return response()->json([
                    'message' => 'Marca não encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:brands,name,' . $id,
                'country_origin' => 'sometimes|required|string|max:255',
            ]);

            $this->service->update($id, $validated);

            $updatedBrand = $this->service->find($id);
            return response()->json($updatedBrand, Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            Log::error('Erro de banco de dados: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar marca',
                'error' => 'Erro de banco de dados'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar marca: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao atualizar marca',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $brand = $this->service->find($id);

            if (!$brand) {
                return response()->json([
                    'message' => 'Marca não encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar se a marca possui modelos associados
            if ($brand->models()->count() > 0) {
                return response()->json([
                    'message' => 'Não é possível excluir esta marca pois existem modelos associados a ela'
                ], Response::HTTP_CONFLICT);
            }

            $this->service->delete($id);

            return response()->json([
                'message' => 'Marca excluída com sucesso'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao excluir marca: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao excluir marca',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Busca as marcas mais populares baseado no número de modelos
     */
    public function popularBrands(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 5);
            
            // Validar limite
            if (!is_numeric($limit) || $limit < 1) {
                $limit = 5;
            }
            
            $popularBrands = $this->service->findPopularBrands((int)$limit);
            
            return response()->json($popularBrands, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Erro ao buscar marcas populares: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar marcas populares',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
