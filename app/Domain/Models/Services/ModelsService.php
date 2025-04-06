<?php

namespace App\Domain\Models\Services;

use App\Domain\Models\Entities\Models;
use Illuminate\Database\Eloquent\Collection;

class ModelsService
{
    public function __construct(
        private Models $entity
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->all();
    }

    public function find($id): ?Models
    {
        return $this->entity->find($id);
    }

    public function create(array $data): Models
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

    public function findByBrand($brandId): Collection
    {
        return $this->entity->where('brand_id', $brandId)->get();
    }
}
