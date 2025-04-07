<?php

namespace App\Domain\Employees\Services;

use App\Domain\Employees\Entities\Employees;
use Illuminate\Database\Eloquent\Collection;

class EmployeesService
{
    public function __construct(
        private Employees $entity
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->all();
    }

    public function find($id): ?Employees
    {
        return $this->entity->find($id);
    }

    public function create(array $data): Employees
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

    public function findByCpf($cpf): ?Employees
    {
        return $this->entity->where('cpf', $cpf)->first();
    }

    public function findByPosition($position): Collection
    {
        return $this->entity->where('position', $position)->get();
    }
}
