<?php

namespace App\Policies;

use App\Models\ServiceImage;
use App\Models\User;

class ServiceImagePolicy
{
    public function delete(User $user, ServiceImage $image): bool
    {
        return $user->isProfessional() && $image->service?->professional?->user_id === $user->id;
    }
}
