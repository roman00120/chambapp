<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        abort_unless(filled(config('services.google.client_id')) && filled(config('services.google.client_secret')), 503, 'Google Login no está configurado todavía.');

        $role = UserRole::tryFrom((string) request('account_type', UserRole::CLIENT->value));
        abort_unless(in_array($role, [UserRole::CLIENT, UserRole::PROFESSIONAL], true), 422, 'Tipo de cuenta no válido.');

        session(['google_account_type' => $role->value]);

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'email' => 'No se pudo iniciar sesión con Google. Inténtalo nuevamente.',
            ]);
        }

        $googleId = trim((string) $googleUser->getId());
        $email = Str::lower(trim((string) $googleUser->getEmail()));

        if ($googleId === '' || $email === '') {
            return redirect()->route('login')->withErrors([
                'email' => 'Google no proporcionó un correo electrónico válido.',
            ]);
        }

        $requestedRole = UserRole::tryFrom((string) session()->pull('google_account_type', UserRole::CLIENT->value));
        $role = in_array($requestedRole, [UserRole::CLIENT, UserRole::PROFESSIONAL], true)
            ? $requestedRole
            : UserRole::CLIENT;

        $user = DB::transaction(function () use ($googleUser, $googleId, $email, $role): User {
            $user = User::query()->where('google_id', $googleId)->first()
                ?? User::query()->where('email', $email)->first();

            if (! $user) {
                $user = User::create([
                    'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Usuario Chambapp',
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar_url' => $googleUser->getAvatar(),
                    'password' => Str::random(40),
                    'role' => $role,
                    'status' => UserStatus::ACTIVE,
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->forceFill([
                    'google_id' => $googleId,
                    'avatar_url' => $googleUser->getAvatar(),
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
            return redirect()->route('login')->withErrors([
                'email' => 'Esta cuenta no está disponible. Contacta a soporte.',
            ]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->route($user->dashboardRoute())->with('status', 'Sesión iniciada con Google.');
    }
}
