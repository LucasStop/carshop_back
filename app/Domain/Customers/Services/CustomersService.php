<?php

namespace App\Domain\Customers\Services;

use App\Domain\Customers\Entities\Customers;
use Illuminate\Database\Eloquent\Collection;

class CustomersService
{
    public function __construct(
        private Customers $entity
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->all();
    }

    public function find($id): ?Customers
    {
        return $this->entity->find($id);
    }

    public function create(array $data): Customers
    {
        return $this->entity->create($data);
    }

    public function update($id, array $data): bool
    {
        return $this->entity->find($id)->update($data);
    }

    public function delete($id): bool
    {
        return $this->entity->find($id)->delete();
    }

    public function findByCpf($cpf): ?Customers
    {
        return $this->entity->where('cpf', $cpf)->first();
    }
}
