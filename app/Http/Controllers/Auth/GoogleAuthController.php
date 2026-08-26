<?php

namespace App\Http\Controllers\Auth;

use App\Enums\IdentityVerificationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\ProfessionalProfile;
use App\Models\User;
use App\Services\LegalAcceptanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $role = UserRole::tryFrom((string) request('account_type', UserRole::CLIENT->value));
        abort_unless(in_array($role, [UserRole::CLIENT, UserRole::PROFESSIONAL], true), 422, 'Tipo de cuenta no válido.');

        session()->forget('google_legal_registration');
        session(['google_account_type' => $role->value, 'google_registration_flow' => false]);

        return $this->googleRedirect();
    }

    public function consent(Request $request, LegalAcceptanceService $legal): View|RedirectResponse
    {
        $role = UserRole::tryFrom((string) $request->query('account_type'));
        abort_unless(in_array($role, [UserRole::CLIENT, UserRole::PROFESSIONAL], true), 422);

        if (! $legal->isRequired()) {
            session(['google_account_type' => $role->value, 'google_registration_flow' => true]);

            return $this->googleRedirect();
        }

        abort_unless($legal->isReady(), 503, 'Los documentos legales definitivos aún no están publicados.');

        return view('auth.google-consent', [
            'accountType' => $role->value,
            'documents' => $legal->publicDocuments($role),
        ]);
    }

    public function registrationRedirect(Request $request, LegalAcceptanceService $legal): RedirectResponse
    {
        $role = UserRole::tryFrom((string) $request->input('account_type'));
        abort_unless(in_array($role, [UserRole::CLIENT, UserRole::PROFESSIONAL], true), 422);
        $data = $request->validate([
            'legal_accepted' => ['required', 'accepted'],
            'legal_documents' => ['required', 'array'],
            'legal_documents.*' => ['required', 'string', 'max:100'],
        ]);
        $documents = $legal->validateRegistration($data, $role);

        session([
            'google_account_type' => $role->value,
            'google_registration_flow' => true,
            'google_legal_registration' => [
                'legal_accepted' => true,
                'legal_documents' => $documents,
                'legal_platform' => 'web_google',
                'legal_ip' => $request->ip(),
                'legal_user_agent' => $request->userAgent(),
            ],
        ]);

        return $this->googleRedirect();
    }

    public function callback(LegalAcceptanceService $legal): RedirectResponse
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
        $role = in_array($requestedRole, [UserRole::CLIENT, UserRole::PROFESSIONAL], true) ? $requestedRole : UserRole::CLIENT;
        $registrationFlow = (bool) session()->pull('google_registration_flow', false);
        $legalRegistration = session()->pull('google_legal_registration');

        $user = DB::transaction(function () use ($googleUser, $googleId, $email, $role, $registrationFlow, $legalRegistration, $legal): User {
            $user = User::query()->where('google_id', $googleId)->first()
                ?? User::query()->where('email', $email)->first();

            if (! $user) {
                abort_unless($registrationFlow, 422, 'Acepta los documentos legales antes de crear una cuenta con Google.');
                $legalData = is_array($legalRegistration) ? $legalRegistration : [];
                $documents = $legal->validateRegistration($legalData, $role);
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
                $legal->record(
                    $user,
                    $documents,
                    (string) ($legalData['legal_platform'] ?? 'web_google'),
                    $legalData['legal_ip'] ?? null,
                    $legalData['legal_user_agent'] ?? null,
                );
            } else {
                $user->forceFill([
                    'google_id' => $googleId,
                    'avatar_url' => $googleUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ])->save();
            }

            if ($user->role === UserRole::PROFESSIONAL && ! $user->professionalProfile()->exists()) {
                $profile = ProfessionalProfile::create([
                    'user_id' => $user->id,
                    'verification_status' => VerificationStatus::UNVERIFIED,
                ]);
                $profile->identityVerification()->create(['status' => IdentityVerificationStatus::NOT_STARTED]);
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

    private function googleRedirect(): RedirectResponse
    {
        abort_unless(filled(config('services.google.client_id')) && filled(config('services.google.client_secret')), 503, 'Google Login no está configurado todavía.');

        return Socialite::driver('google')->scopes(['openid', 'profile', 'email'])->redirect();
    }
}
