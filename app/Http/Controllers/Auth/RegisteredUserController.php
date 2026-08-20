<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = UserRole::from($data['account_type']);

        $user = DB::transaction(function () use ($data, $role): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'role' => $role,
                'status' => UserStatus::ACTIVE,
            ]);

            if ($role === UserRole::PROFESSIONAL) {
                ProfessionalProfile::create([
                    'user_id' => $user->id,
                    'verification_status' => VerificationStatus::UNVERIFIED,
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $user->sendEmailVerificationNotification();

        return redirect()
            ->route($user->dashboardRoute())
            ->with('status', 'Tu cuenta fue creada. Revisa tu correo para verificarla.');
    }
}
