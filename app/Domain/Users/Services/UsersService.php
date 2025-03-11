<?php

namespace App\Domain\Users\Services;

use App\Domain\Roles\Entities\Roles;
use App\Domain\Users\Entities\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Domain\Messages\Services\MessagesService;

class UsersService
{
    public function __construct(
        private User $entity,
        private Roles $roles,
        private MessagesService $messagesService
    ) {}

    public function findRoleBySlug($slug): ?Roles
    {
        return $this->roles->where('slug', $slug)
            ->where('id', '!=', $this->roles::PROFESSIONAL)
            ->first();
    }

    public function selectRoles(): Collection
    {
        return $this->roles
            ->where('id', '!=', $this->roles::PROFESSIONAL)
            ->get(
                ['id', 'name', 'slug']
            );
    }

    public function resume(array $data): array
    {
        $query = $this->entity->whereHas('compUser', function ($query) use ($data) {
            $query->where('company_id', $data['company_id'])
                ->where('role_id', '!=', $this->roles::PROFESSIONAL);
        });

        $total = $query->count();
        $actives = (clone $query)->whereHas('compUser', function ($query) {
            $query->where('status', 'active');
        })->count();
        $inactives = (clone $query)->whereHas('compUser', function ($query) {
            $query->where('status', 'inactive');
        })->count();

        return [
            'total' => $total,
            'actives' => $actives,
            'inactives' => $inactives,
        ];
    }

    public function paginate(array $data): LengthAwarePaginator
    {
        $query = $this->entity->whereHas('compUser', function ($query) use ($data) {
            $query->where('company_id', $data['company_id'])
                ->where('role_id', '!=', $this->roles::PROFESSIONAL);
        });

        if (!empty($data['search'])) {
            $query->where(function ($query) use ($data) {
                $query->where('name', 'like', "%{$data['search']}%")
                    ->orWhere('email', 'like', "%{$data['search']}%");
            });
        }

        return $query->with(['compUser' => function ($query) use ($data) {
            $query->where('company_id', $data['company_id']);
        }])
            ->paginate($data['per_page'] ?? 80);
    }

    public function findByHash($hash): ?User
    {
        return $this->entity->where('hash', $hash)->first();
    }

    public function all(): Collection
    {
        return $this->entity->all();
    }

    public function find($id): ?User
    {
        return $this->entity->find($id);
    }

    public function findByEmail($email): ?User
    {
        return $this->entity->where('email', $email)->first();
    }

    public function create(array $data): User
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

    public function sendMessage(
        $user,
        $createdPassword,
        $request,
    ): void {
        $message = "Olá, " . $user->name . "!\n\n";
        $message .= "Seja bem-vindo à {$request->company?->name}! Estamos muito felizes em tê-lo conosco.\n\n";
        $message .= "Para acessar sua conta, criamos uma senha para o seu primeiro acesso. Sua senha é: \n";
        $message .= "🔑 **" . $createdPassword . "**\n\n";
        $message .= "Recomendamos que você altere a senha assim que acessar o sistema pela primeira vez.\n";
        $message .= "Caso tenha qualquer dúvida ou precise de assistência, nossa equipe está à disposição.\n\n";
        $message .= "Atenciosamente, \nEquipe AgendaMais.";

        $this->messagesService->create([
            'company_id' => $request->company->id,
            'user_id' => $user->id,
            'reference_id' => $user->id,
            'recipient' => $user->phone,
            'message' => $message,
            'priority' => 'NORMAL',
            'type' => 'whatsapp',
        ]);
    }
}
