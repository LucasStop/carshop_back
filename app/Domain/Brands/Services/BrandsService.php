<?php

namespace App\Domain\Brands\Services;

use App\Domain\Brands\Entities\Brands;
use Illuminate\Database\Eloquent\Collection;

class BrandsService
{
    public function __construct(
        private Brands $entity
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->all();
    }

    public function find($id): ?Brands
    {
        return $this->entity->find($id);
    }

    public function create(array $data): Brands
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

    // public function findByCnpj($cnpj): bool
    // {
    //     return $this->entity->where('cnpj', $cnpj)->exists();
    // }
}
