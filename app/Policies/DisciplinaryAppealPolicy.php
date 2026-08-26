<?php

namespace App\Policies;

use App\Models\DisciplinaryAppeal;
use App\Models\User;

class DisciplinaryAppealPolicy
{
    public function view(User $user, DisciplinaryAppeal $appeal): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $appeal->user_id === $user->id;
    }

    public function resolve(User $user, DisciplinaryAppeal $appeal): bool
    {
        return $user->isAdmin() && $user->id !== $appeal->user_id;
    }
}
