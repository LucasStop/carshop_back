<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Sales\Services\SalesService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SalesController extends Controller
{
    public function __construct(
        private SalesService $service
    ) {}

    public function index(): JsonResponse
    {
        $sales = $this->service->all();

        return response()->json($sales, Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'car_id' => 'required|exists:cars,id',
            'customer_id' => 'required|exists:customers,id',
            'employee_id' => 'required|exists:employees,id',
            'sale_date' => 'required|date',
            'final_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $sale = $this->service->create($request->all());

        return response()->json($sale, Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $sale = $this->service->find($id);

        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($sale, Response::HTTP_OK);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'car_id' => 'sometimes|required|exists:cars,id',
            'customer_id' => 'sometimes|required|exists:customers,id',
            'employee_id' => 'sometimes|required|exists:employees,id',
            'sale_date' => 'sometimes|required|date',
            'final_price' => 'sometimes|required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $sale = $this->service->find($id);

        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], Response::HTTP_NOT_FOUND);
        }

        $this->service->update($id, $request->all());

        return response()->json($this->service->find($id), Response::HTTP_OK);
    }

    public function delete(int $id): JsonResponse
    {
        $sale = $this->service->find($id);

        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], Response::HTTP_NOT_FOUND);
        }

        $this->service->delete($id);

        return response()->json(['message' => 'Sale deleted successfully'], Response::HTTP_OK);
    }

    public function byCustomer(int $customerId): JsonResponse
    {
        $sales = $this->service->findByCustomer($customerId);

        return response()->json($sales, Response::HTTP_OK);
    }

    public function byEmployee(int $employeeId): JsonResponse
    {
        $sales = $this->service->findByEmployee($employeeId);

        return response()->json($sales, Response::HTTP_OK);
    }

    public function byCar(int $carId): JsonResponse
    {
        $sale = $this->service->findByCar($carId);

        if (!$sale) {
            return response()->json(['message' => 'Sale not found for this car'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($sale, Response::HTTP_OK);
    }
}
