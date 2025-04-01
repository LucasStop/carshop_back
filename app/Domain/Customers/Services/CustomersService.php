<?php

namespace App\Domain\Customers\Services;

use App\Domain\Customers\Entities\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomersService
{
    public function __construct(
        private Customer $customer
    ) {}

    public function getAllCustomers()
    {
        return $this->customer->paginate(10);
    }

    public function getCustomerById($id)
    {
        $customer = $this->customer->find($id);

        if (!$customer) {
            throw new ModelNotFoundException('Cliente não encontrado');
        }

        return $customer;
    }

    public function createCustomer(array $data)
    {
        return $this->customer->create($data);
    }

    public function updateCustomer($id, array $data)
    {
        $customer = $this->getCustomerById($id);
        $customer->update($data);

        return $customer;
    }

    public function deleteCustomer($id)
    {
        $customer = $this->getCustomerById($id);
        return $customer->delete();
    }
}
