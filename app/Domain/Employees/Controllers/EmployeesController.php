<?php

namespace App\Domain\Employees\Controllers;

use App\Domain\Employees\Services\EmployeesService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EmployeesController extends Controller
{
    public function __construct(
        private EmployeesService $service
    ) {}

    public function index(): JsonResponse
    {
        $employees = $this->service->all();

        return response()->json($employees, Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'position' => 'nullable|string|max:50',
            'email' => 'nullable|email|unique:employees,email|max:100',
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14|unique:employees,cpf',
            'rg' => 'nullable|string|max:20|unique:employees,rg',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:200',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
        ]);

        $employee = $this->service->create($request->all());

        return response()->json($employee, Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $employee = $this->service->find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($employee, Response::HTTP_OK);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'position' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100|unique:employees,email,'.$id,
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14|unique:employees,cpf,'.$id,
            'rg' => 'nullable|string|max:20|unique:employees,rg,'.$id,
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:200',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
        ]);

        $employee = $this->service->find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        $this->service->update($id, $request->all());

        return response()->json($this->service->find($id), Response::HTTP_OK);
    }

    public function delete(int $id): JsonResponse
    {
        $employee = $this->service->find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        $this->service->delete($id);

        return response()->json(['message' => 'Employee deleted successfully'], Response::HTTP_OK);
    }

    public function findByCpf(string $cpf): JsonResponse
    {
        $employee = $this->service->findByCpf($cpf);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($employee, Response::HTTP_OK);
    }

    public function findByPosition(string $position): JsonResponse
    {
        $employees = $this->service->findByPosition($position);

        return response()->json($employees, Response::HTTP_OK);
    }
}
