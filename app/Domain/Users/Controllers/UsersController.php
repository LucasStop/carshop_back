<?php

namespace App\Domain\Users\Controllers;

use App\Domain\Companies\Services\CompaniesService;
use App\Domain\Users\Services\UsersService;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    public function __construct(
        private UsersService $service,
        private CompaniesService $companiesService
    ) {}

    public function roles(): JsonResponse
    {
        try {
            $data = $this->service->selectRoles();

            return response()->json($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function resume(Request $request): JsonResponse
    {
        try {
            $data = $this->service->resume($request->all());

            return response()->json($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->service->paginate($request->all());

            $return = $data->map(function ($item) {
                return [
                    'hash' => $item->hash,
                    'name' => $item->name,
                    'photo' => $item->photo,
                    'email' => $item->email,
                    'password' => $item->password,
                    'phone' => $item->phone,
                    'cpf' => $item->cpf,
                    'status' => $item->compUser?->status,
                    'role_slug' => $item->compUser?->role?->slug,
                    'role_name' => $item->compUser?->role?->name,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            return response()->json(
                pagination($data, $return),
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id): JsonResponse
    {
        try {

            $user = $this->service->find($id);

            return response()->json([
                'user' => $user
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                'company_id' => 'required|integer',
                'name' => 'required|string|max:255',
                'photo' => 'nullable|string',
                'email' => 'required|email',
                'phone' => 'nullable|string',
                'cpf' => 'nullable|string',
                'role_slug' => 'required|string',
                'status' => 'required|string',
                'password' => 'required|string|min:6',
            ]);

            $validatedData['phone'] = preg_replace('/[^0-9]/', '', $validatedData['phone']);
            $validatedData['password'] = Hash::make($validatedData['password']);

            $role = $this->service->findRoleBySlug($validatedData['role_slug']);
            if (!$role) {
                throw new \Exception('O cargo informado não existe.');
            }
            $user = $this->service->findByEmail($validatedData['email']);
            if ($user) {
                throw new \Exception('Já existe um usuário com esse e-mail.');
            }
            $user = $this->service->create($validatedData);
            $this->companiesService->vinculateUser($user->id, $role->id, $validatedData['company_id'], $validatedData['status']);

            $this->service->sendMessage(
                $user,
                $request->password,
                $request,
            );

            DB::commit();

            return response()->json([
                'success' => 'Usuário criado com sucesso!',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $hash): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'photo' => 'nullable|file',
                'email' => 'required|email',
                'phone' => 'nullable|string',
                'cpf' => 'nullable|string',
                'role_slug' => 'required|string',
                'status' => 'required|string'
            ]);

            $user = $this->service->findByHash($hash);
            if (!$user) {
                throw new ModelNotFoundException('Usuário não encontrado.');
            }

            $userEmail = $this->service->findByEmail($validatedData['email']);
            if ($userEmail && $userEmail->id !== $user->id) {
                throw new \Exception('Já existe um usuário com esse e-mail.');
            }

            $role = $this->service->findRoleBySlug($validatedData['role_slug']);
            if (!$role) {
                throw new \Exception('O cargo informado não existe.');
            }

            if (empty($validatedData['password'])) {
                unset($validatedData['password']);
            } else {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            if ($request->file('photo')) {
                $path = Storage::disk('AgendaAi')->put(env('WAS_CAMINHO'), $request->file('photo'));
                $validatedData['photo'] = str_replace('agendamais/', '', $path);
            }

            $this->service->update($user->id, $validatedData);

            $compUser = $user
                ->compUser()
                ->where('company_id', $request->company_id)
                ->first();

            $compUser->update(
                [
                    'role_id' => $role->id,
                    'status' => $validatedData['status']
                ]
            );

            DB::commit();

            return response()->json([
                'success' => 'Usuário atualizado com sucesso!',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Request $request, $hash): JsonResponse
    {
        try {
            $user = $this->service->findByHash($hash);
            if (!$user) {
                throw new ModelNotFoundException('Usuário não encontrado.');
            }
            $user->compUser()
                ->where('company_id', $request->company_id)
                ->delete();
            $this->service->delete($user->id);

            return response()->json([
                'success' => 'Usuário deletado com sucesso!',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
