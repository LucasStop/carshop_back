<?php

namespace App\Domain\Customers\Controllers;

use App\Domain\Customers\Services\CustomersService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CustomersController extends Controller
{
    public function __construct(
        private CustomersService $service
    ) {}

    public function index(): JsonResponse
    {
        $customers = $this->service->all();

        return response()->json($customers, Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:customers,email|max:100',
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14|unique:customers,cpf',
            'rg' => 'nullable|string|max:20|unique:customers,rg',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:200',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
        ]);

        $customer = $this->service->create($request->all());

        return response()->json($customer, Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $customer = $this->service->find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($customer, Response::HTTP_OK);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => 'nullable|email|max:100|unique:customers,email,'.$id,
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14|unique:customers,cpf,'.$id,
            'rg' => 'nullable|string|max:20|unique:customers,rg,'.$id,
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:200',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
        ]);

        $customer = $this->service->find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        $this->service->update($id, $request->all());

        return response()->json($this->service->find($id), Response::HTTP_OK);
    }

    public function delete(int $id): JsonResponse
    {
        $customer = $this->service->find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        $this->service->delete($id);

        return response()->json(['message' => 'Customer deleted successfully'], Response::HTTP_OK);
    }

    public function findByCpf(string $cpf): JsonResponse
    {
        $customer = $this->service->findByCpf($cpf);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($customer, Response::HTTP_OK);
    }
}
