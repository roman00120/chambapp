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

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        abort_unless(filled(config('services.google.client_id')) && filled(config('services.google.client_secret')), 503, 'Google Login no está configurado todavía.');

        session(['google_account_type' => request('account_type', 'client')]);

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();
        $role = UserRole::tryFrom((string) session()->pull('google_account_type', 'client')) ?? UserRole::CLIENT;

        $user = DB::transaction(function () use ($googleUser, $role): User {
            $user = User::query()->where('google_id', $googleUser->getId())->orWhere('email', $googleUser->getEmail())->first();

            if (! $user) {
                $user = User::create([
                    'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Usuario Chambapp',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar_url' => $googleUser->getAvatar(),
                    'password' => Str::random(40),
                    'role' => $role,
                    'status' => UserStatus::ACTIVE,
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'avatar_url' => $googleUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ])->save();
            }

            if ($user->role === UserRole::PROFESSIONAL && ! $user->professionalProfile()->exists()) {
                ProfessionalProfile::create(['user_id' => $user->id, 'verification_status' => VerificationStatus::UNVERIFIED]);
            }

            return $user;
        });

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->route($user->dashboardRoute())->with('status', 'Sesión iniciada con Google.');
    }
}
