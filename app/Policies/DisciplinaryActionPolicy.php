<?php

namespace App\Policies;

use App\Models\DisciplinaryAction;
use App\Models\User;

class DisciplinaryActionPolicy
{
    public function view(User $user, DisciplinaryAction $action): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $action->user_id === $user->id;
    }

    public function appeal(User $user, DisciplinaryAction $action): bool
    {
        return $action->user_id === $user->id && $action->isActive();
    }

    public function revoke(User $user, DisciplinaryAction $action): bool
    {
        return $user->isAdmin() && $user->id !== $action->user_id;
    }
}
