<?php

namespace App\Domain\Cars\Controllers;

use App\Domain\Cars\Services\CarsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CarsController extends Controller
{
    public function __construct(
        private CarsService $service
    ) {}

    public function index(): JsonResponse
    {
        $cars = $this->service->all();

        return response()->json($cars, Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'model_id' => 'required|exists:models,id',
            'vin' => 'required|string|max:50|unique:cars,vin',
            'color' => 'nullable|string|max:30',
            'manufacture_year' => 'nullable|integer',
            'mileage' => 'nullable|integer',
            'status' => 'nullable|in:available,sold,reserved,maintenance',
            'inclusion_date' => 'nullable|date',
        ]);

        $car = $this->service->create($request->all());

        return response()->json($car, Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $car = $this->service->find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($car, Response::HTTP_OK);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'model_id' => 'sometimes|required|exists:models,id',
            'vin' => 'sometimes|required|string|max:50|unique:cars,vin,'.$id,
            'color' => 'nullable|string|max:30',
            'manufacture_year' => 'nullable|integer',
            'mileage' => 'nullable|integer',
            'status' => 'nullable|in:available,sold,reserved,maintenance',
            'inclusion_date' => 'nullable|date',
        ]);

        $car = $this->service->find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }

        $this->service->update($id, $request->all());

        return response()->json($this->service->find($id), Response::HTTP_OK);
    }

    public function delete(int $id): JsonResponse
    {
        $car = $this->service->find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }

        $this->service->delete($id);

        return response()->json(['message' => 'Car deleted successfully'], Response::HTTP_OK);
    }

    public function byModel(int $modelId): JsonResponse
    {
        $cars = $this->service->findByModel($modelId);
        
        return response()->json($cars, Response::HTTP_OK);
    }

    public function byStatus(string $status): JsonResponse
    {
        $cars = $this->service->findByStatus($status);
        
        return response()->json($cars, Response::HTTP_OK);
    }
}
