<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\ProfessionalProfile;
use App\Models\User;
use App\Services\UserRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

    public function google(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_token' => ['required', 'string', 'max:4096'],
            'device_name' => ['required', 'string', 'max:255'],
            'role' => ['sometimes', 'string', 'in:client,professional'],
        ]);

        $clientId = trim((string) config('services.google.client_id'));
        if ($clientId === '') {
            return response()->json([
                'message' => 'El inicio de sesiÃ³n con Google no estÃ¡ configurado.',
                'errors' => (object) [],
                'code' => 'GOOGLE_NOT_CONFIGURED',
            ], 503);
        }

        $google = Http::acceptJson()
            ->timeout(8)
            ->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $data['id_token']]);

        $claims = $google->successful() ? $google->json() : null;
        $issuer = is_array($claims) ? (string) ($claims['iss'] ?? '') : '';
        $emailVerified = is_array($claims) && filter_var(
            $claims['email_verified'] ?? false,
            FILTER_VALIDATE_BOOL,
        );

        if (! is_array($claims)
            || ! hash_equals($clientId, (string) ($claims['aud'] ?? ''))
            || ! in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)
            || ! $emailVerified
            || (int) ($claims['exp'] ?? 0) <= now()->timestamp
        ) {
            return response()->json([
                'message' => 'Google no pudo validar esta cuenta. IntÃ©ntalo nuevamente.',
                'errors' => (object) [],
                'code' => 'INVALID_GOOGLE_TOKEN',
            ], 401);
        }

        $googleId = trim((string) ($claims['sub'] ?? ''));
        $email = Str::lower(trim((string) ($claims['email'] ?? '')));
        if ($googleId === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'Google no proporcionÃ³ una cuenta vÃ¡lida.',
                'errors' => (object) [],
                'code' => 'INVALID_GOOGLE_ACCOUNT',
            ], 401);
        }

        $role = UserRole::tryFrom($data['role'] ?? UserRole::CLIENT->value) ?? UserRole::CLIENT;
        $user = DB::transaction(function () use ($claims, $googleId, $email, $role): User {
            $user = User::query()->where('google_id', $googleId)->first()
                ?? User::query()->where('email', $email)->first();

            if (! $user) {
                $user = User::create([
                    'name' => trim((string) ($claims['name'] ?? '')) ?: 'Usuario Chambapp',
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar_url' => $claims['picture'] ?? null,
                    'password' => Str::random(40),
                    'role' => $role,
                    'status' => UserStatus::ACTIVE,
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->forceFill([
                    'google_id' => $googleId,
                    'avatar_url' => $claims['picture'] ?? $user->avatar_url,
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ])->save();
            }

            if ($user->role === UserRole::PROFESSIONAL && ! $user->professionalProfile()->exists()) {
                ProfessionalProfile::create([
                    'user_id' => $user->id,
                    'verification_status' => VerificationStatus::UNVERIFIED,
                ]);
            }

            return $user;
        });

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
            'message' => 'SesiÃ³n iniciada con Google.',
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
