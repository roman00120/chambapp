<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendPasswordResetLinkRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(SendPasswordResetLinkRequest $request): RedirectResponse
    {
        Password::sendResetLink($request->validated());

        return back()->with('status', 'Si existe una cuenta con ese correo, recibirás instrucciones para restablecer tu contraseña.');
    }
}
