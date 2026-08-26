<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Services\LegalAcceptanceService;
use App\Services\UserRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(LegalAcceptanceService $legal): View
    {
        return view('auth.register', [
            'legalRegistrationRequired' => $legal->isRequired(),
            'legalRegistrationReady' => $legal->isReady(),
            'clientLegalDocuments' => $legal->publicDocuments(UserRole::CLIENT),
            'professionalLegalDocuments' => $legal->publicDocuments(UserRole::PROFESSIONAL),
        ]);
    }

    public function store(RegisterUserRequest $request, UserRegistrationService $registrations): RedirectResponse
    {
        $data = $request->validated();
        $data['legal_platform'] = 'web';
        $data['legal_ip'] = $request->ip();
        $data['legal_user_agent'] = $request->userAgent();
        $user = $registrations->register($data);

        Auth::login($user);
        $request->session()->regenerate();
        $user->sendEmailVerificationNotification();

        return redirect()
            ->route($user->dashboardRoute())
            ->with('status', 'Tu cuenta fue creada. Revisa tu correo para verificarla.');
    }
}
