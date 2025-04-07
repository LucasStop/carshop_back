<?php

namespace App\Domain\Cars\Services;

use App\Domain\Cars\Entities\Cars;
use Illuminate\Database\Eloquent\Collection;

class CarsService
{
    public function __construct(
        private Cars $entity
    ) {
    }

    public function all(): Collection
    {
        return $this->entity->all();
    }

    public function find($id): ?Cars
    {
        return $this->entity->find($id);
    }

    public function create(array $data): Cars
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

    public function findByModel($modelId): Collection
    {
        return $this->entity->where('model_id', $modelId)->get();
    }

    public function findByStatus($status): Collection
    {
        return $this->entity->where('status', $status)->get();
    }
}
