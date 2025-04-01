<?php

namespace App\Domain\Customers\Controllers;

use App\Domain\Customers\Requests\CustomerRequest;
use App\Domain\Customers\Services\CustomersService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function __construct(
        private CustomersService $customersService
    ) {}

    public function index()
    {
        return response()->json($this->customersService->getAllCustomers());
    }

    public function store(CustomerRequest $request)
    {
        $customer = $this->customersService->createCustomer($request->validated());
        return response()->json($customer, 201);
    }

    public function show($id)
    {
        $customer = $this->customersService->getCustomerById($id);
        return response()->json($customer);
    }

    public function update(CustomerRequest $request, $id)
    {
        $customer = $this->customersService->updateCustomer($id, $request->validated());
        return response()->json($customer);
    }

    public function destroy($id)
    {
        $this->customersService->deleteCustomer($id);
        return response()->json(null, 204);
    }

    public function select(Request $request)
    {
        // ...existing code...
    }

    public function users()
    {
        // ...existing code...
    }
}
