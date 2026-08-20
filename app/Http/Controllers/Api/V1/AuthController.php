<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\UserRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, UserRegistrationService $registrations): JsonResponse
    {
        $data = $request->validated();
        $user = $registrations->register([...$data, 'account_type' => $data['role']]);
        $user->sendEmailVerificationNotification();
        $token = $user->createToken($data['device_name'])->plainTextToken;

        return response()->json([
            'data' => ['token' => $token, 'user' => new UserResource($user)],
            'message' => 'Cuenta creada correctamente.',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Las credenciales proporcionadas no son válidas.',
                'errors' => (object) [],
                'code' => 'INVALID_CREDENTIALS',
            ], 401);
        }

        if (! $user->isActive()) {
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Tu cuenta no se encuentra disponible actualmente.',
                'errors' => (object) [],
                'code' => 'ACCOUNT_UNAVAILABLE',
            ], 403);
        }

        $user->load('professionalProfile');
        $token = $user->createToken($data['device_name'])->plainTextToken;

        return response()->json([
            'data' => ['token' => $token, 'user' => new UserResource($user)],
            'message' => 'Sesión iniciada correctamente.',
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load('professionalProfile'));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['data' => null, 'message' => 'Token revocado correctamente.']);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['data' => null, 'message' => 'Todos los tokens fueron revocados.']);
    }
}
