<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserReport;

class UserReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canActAsClient() || $user->canActAsProfessional();
    }

    public function view(User $user, UserReport $report): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $report->reporter_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->canActAsClient() || $user->canActAsProfessional();
    }

    public function resolve(User $user, UserReport $report): bool
    {
        return $user->isAdmin() && $user->id !== $report->reporter_id && $user->id !== $report->reported_id;
    }
}
