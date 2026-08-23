<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActive()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $user->currentAccessToken()?->delete();

                return response()->json([
                    'message' => 'Tu cuenta no se encuentra disponible actualmente.',
                    'errors' => (object) [],
                    'code' => 'ACCOUNT_UNAVAILABLE',
                ], 403);
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Tu cuenta no se encuentra disponible actualmente. Contacta a soporte si consideras que se trata de un error.');
        }

        return $next($request);
    }
}
