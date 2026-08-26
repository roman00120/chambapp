<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401, 'Debes iniciar sesión.');
        }

        $allowed = false;
        foreach ($roles as $role) {
            $roleEnum = UserRole::tryFrom($role);
            if ($roleEnum === UserRole::ADMIN && $user->isAdmin()) {
                $allowed = true;
                break;
            }
            if ($roleEnum === UserRole::CLIENT && $user->canActAsClient()) {
                $allowed = true;
                break;
            }
            if ($roleEnum === UserRole::PROFESSIONAL && $user->canActAsProfessional()) {
                $allowed = true;
                break;
            }
        }

        if (! $allowed) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
