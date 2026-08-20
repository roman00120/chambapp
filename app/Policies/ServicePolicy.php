<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function view(User $user, Service $service): bool
    {
        return $user->isProfessional() && $service->professional?->user_id === $user->id;
    }

    public function update(User $user, Service $service): bool
    {
        return $user->isProfessional() && $service->professional?->user_id === $user->id;
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->update($user, $service);
    }
}
