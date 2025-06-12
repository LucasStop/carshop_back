<?php

namespace App\Domain\Roles\Services;

use App\Domain\Roles\Entities\Roles;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class RolesService
{
    public function __construct(
        private Roles $entity,
    ) {}

    public function all(): Collection
    {
        return $this->entity->all();
    }

    public function find($id): ?Roles
    {
        return $this->entity->find($id);
    }

    private function generateSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->entity->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function create(array $data): Roles
    {
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }

        return $this->entity->create($data);
    }

    public function update($id, array $data): bool
    {
        $role = $this->entity->find($id);

        if (!$role) {
            return false;
        }

        // Se o nome mudou e não foi fornecido um slug, gera um novo
        if (isset($data['name']) && $data['name'] !== $role->name) {
            if (!isset($data['slug']) || empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['name']);
            }
        }

        return $role->update($data);
    }

    public function delete($id): bool
    {
        $role = $this->entity->find($id);

        if (!$role) {
            return false;
        }

        return $role->delete();
    }
}
