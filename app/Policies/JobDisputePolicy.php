<?php

namespace App\Policies;

use App\Enums\JobDisputeStatus;
use App\Enums\JobStatus;
use App\Models\JobRequest;
use App\Models\User;

class JobDisputePolicy
{
    public function create(User $user, JobRequest $jobRequest): bool
    {
        return $user->isClient()
            && $jobRequest->client_id === $user->getKey()
            && $jobRequest->status === JobStatus::AWAITING_CONFIRMATION
            && ! $jobRequest->dispute()->where('status', '!=', JobDisputeStatus::REJECTED->value)->exists();
    }
}
