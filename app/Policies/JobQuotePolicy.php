<?php

namespace App\Policies;

use App\Enums\JobStatus;
use App\Enums\QuoteStatus;
use App\Enums\UserRole;
use App\Models\JobQuote;
use App\Models\JobRequest;
use App\Models\User;

class JobQuotePolicy
{
    public function view(User $user, JobQuote $jobQuote): bool
    {
        $job = $jobQuote->jobRequest;

        return $job && ($this->isClient($user, $job) || $this->isProfessional($user, $job));
    }

    public function create(User $user, JobRequest $jobRequest): bool
    {
        return $this->isProfessional($user, $jobRequest)
            && in_array($jobRequest->status, [JobStatus::PENDING, JobStatus::ACCEPTED, JobStatus::MATCHED, JobStatus::AWAITING_QUOTE], true);
    }

    public function accept(User $user, JobQuote $jobQuote): bool
    {
        return $this->isClient($user, $jobQuote->jobRequest)
            && $jobQuote->status === QuoteStatus::PENDING;
    }

    public function reject(User $user, JobQuote $jobQuote): bool
    {
        return $this->isClient($user, $jobQuote->jobRequest)
            && $jobQuote->status === QuoteStatus::PENDING;
    }

    private function isClient(User $user, JobRequest $jobRequest): bool
    {
        return $user->role === UserRole::CLIENT && $jobRequest->client_id === $user->getKey();
    }

    private function isProfessional(User $user, JobRequest $jobRequest): bool
    {
        return $user->role === UserRole::PROFESSIONAL
            && $jobRequest->professional?->user_id === $user->getKey();
    }
}
