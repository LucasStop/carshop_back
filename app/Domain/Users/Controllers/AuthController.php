<?php

namespace App\Domain\Users\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Domain\Users\Entities\Users;
use App\Domain\Addresses\Services\AddressesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    private AddressesService $addressesService;

    public function __construct(AddressesService $addressesService)
    {
        $this->addressesService = $addressesService;
    }

    /**
     * Login do usuário e geração de token JWT
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if (!$token = Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Credenciais inválidas',
                    'errors' => ['email' => ['Email ou senha incorretos']]
                ], Response::HTTP_UNAUTHORIZED);
            }

            /** @var \App\Domain\Users\Entities\Users $user */
            $user = Auth::user();
            if ($user) {
                $user->load(['role', 'address']);
            }

            return response()->json([
                'message' => 'Login realizado com sucesso',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires_in' => Auth::factory()->getTTL() * 60
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao fazer login: ' . $e->getMessage());

            return response()->json([
                'message' => 'Erro ao fazer login',
                'errors' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Registrar um novo usuário
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'role_id' => 'required|exists:roles,id',
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:100|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'phone' => 'nullable|string|max:20',
                'cpf' => 'nullable|string|max:14|unique:users,cpf',
                'rg' => 'nullable|string|max:20|unique:users,rg',
                'birth_date' => 'nullable|date',
                'address' => 'nullable|array',
                'address.address' => 'nullable|string|max:200',
                'address.number' => 'nullable|string|max:10',
                'address.complement' => 'nullable|string|max:50',
                'address.city' => 'nullable|string|max:50',
                'address.state' => 'nullable|string|max:2',
                'address.zip_code' => 'nullable|string|max:10',
            ]);

            // Criar o usuário no banco de dados com todos os campos obrigatórios
            $user = new Users();
            $user->role_id = $validated['role_id'];
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = Hash::make($validated['password']);

            // Campos opcionais
            if (isset($validated['phone'])) $user->phone = $validated['phone'];
            if (isset($validated['cpf'])) $user->cpf = $validated['cpf'];
            if (isset($validated['rg'])) $user->rg = $validated['rg'];
            if (isset($validated['birth_date'])) $user->birth_date = $validated['birth_date'];

            $user->save();

            // Se houver dados de endereço, criar o relacionamento
            if (isset($validated['address']) && is_array($validated['address'])) {
                $addressData = $validated['address'];
                $addressData['user_id'] = $user->id;

                $this->addressesService->create($addressData);
            }

            // Recarregar o usuário com os relacionamentos
            $user->load(['role', 'address']);

            // Gerar token JWT
            $token = JWTAuth::fromUser($user);

            DB::commit();
            return response()->json([
                'message' => 'Usuário registrado com sucesso',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires_in' => Auth::factory()->getTTL() * 60
                ]
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            // Log do erro para depuração
            Log::error('Erro ao registrar usuário: ' . $e->getMessage());

            return response()->json([
                'message' => 'Erro ao registrar usuário',
                'errors' => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Obter o perfil do usuário autenticado
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        try {
            /** @var \App\Domain\Users\Entities\Users $user */
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'message' => 'Usuário não autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $user->load(['role', 'address']);

            return response()->json($user);
        } catch (Exception $e) {
            Log::error('Erro ao obter perfil do usuário: ' . $e->getMessage());

            return response()->json([
                'message' => 'Erro ao obter perfil do usuário',
                'errors' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Atualizar token JWT
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        try {
            return response()->json([
                'user' => Auth::user(),
                'authorization' => [
                    'token' => Auth::refresh(),
                    'type' => 'bearer',
                    'expires_in' => Auth::factory()->getTTL() * 60
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar token: ' . $e->getMessage());

            return response()->json([
                'message' => 'Erro ao atualizar token',
                'errors' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
