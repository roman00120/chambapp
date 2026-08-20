<?php

namespace App\Policies;

use App\Models\ProfessionalProfile;
use App\Models\User;

class ProfessionalProfilePolicy
{
    public function view(User $user, ProfessionalProfile $profile): bool
    {
        return $user->isProfessional() && $profile->user_id === $user->id;
    }

    public function update(User $user, ProfessionalProfile $profile): bool
    {
        return $user->isProfessional() && $profile->user_id === $user->id;
    }
}
