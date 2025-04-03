<?php

namespace App\Domain\Brands\Controllers;

use App\Domain\Brands\Services\BrandsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class BrandsController extends Controller
{
    public function __construct(
        private BrandsService $service
    ) {}

    public function index(): JsonResponse
    {
        $brands = $this->service->all();

        return response()->json($brands, Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country_origin' => 'required|string|max:255',
        ]);

        $brand = $this->service->create($request->all());

        return response()->json($brand, Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $brand = $this->service->find($id);

        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($brand, Response::HTTP_OK);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'country_origin' => 'sometimes|required|string|max:255',
        ]);

        $brand = $this->service->find($id);

        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], Response::HTTP_NOT_FOUND);
        }

        $brand->update($request->all());

        return response()->json($brand, Response::HTTP_OK);
    }

    public function delete(int $id): JsonResponse
    {
        $brand = $this->service->find($id);

        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], Response::HTTP_NOT_FOUND);
        }

        $this->service->delete($id);

        return response()->json(['message' => 'Brand deleted successfully'], Response::HTTP_OK);
    }
}
