<?php

namespace App\Domain\Models\Controllers;

use App\Domain\Models\Services\ModelsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ModelsController extends Controller
{
    public function __construct(
        private ModelsService $service
    ) {}

    public function index(): JsonResponse
    {
        $models = $this->service->all();

        return response()->json($models, Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:50',
            'year_model' => 'nullable|integer',
            'engine' => 'nullable|string|max:50',
            'power' => 'nullable|integer',
            'base_price' => 'nullable|numeric|min:0',
        ]);

        $model = $this->service->create($request->all());

        return response()->json($model, Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $model = $this->service->find($id);

        if (!$model) {
            return response()->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($model, Response::HTTP_OK);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'brand_id' => 'sometimes|required|exists:brands,id',
            'name' => 'sometimes|required|string|max:50',
            'year_model' => 'nullable|integer',
            'engine' => 'nullable|string|max:50',
            'power' => 'nullable|integer',
            'base_price' => 'nullable|numeric|min:0',
        ]);

        $model = $this->service->find($id);

        if (!$model) {
            return response()->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        $this->service->update($id, $request->all());

        return response()->json($this->service->find($id), Response::HTTP_OK);
    }

    public function delete(int $id): JsonResponse
    {
        $model = $this->service->find($id);

        if (!$model) {
            return response()->json(['message' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        $this->service->delete($id);

        return response()->json(['message' => 'Model deleted successfully'], Response::HTTP_OK);
    }

    public function byBrand(int $brandId): JsonResponse
    {
        $models = $this->service->findByBrand($brandId);

        return response()->json($models, Response::HTTP_OK);
    }
}
