<?php

namespace App\Policies;

use App\Enums\JobStatus;
use App\Models\JobRequest;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, JobRequest $jobRequest): bool
    {
        return $user->canActAsClient()
            && $jobRequest->client_id === $user->getKey()
            && $jobRequest->professional?->user_id !== $user->getKey()
            && $jobRequest->status === JobStatus::COMPLETED
            && ! $jobRequest->review()->exists();
    }

    public function view(User $user, Review $review): bool
    {
        return $review->client_id === $user->getKey()
            || $review->professional?->user_id === $user->getKey();
    }

    public function update(User $user, Review $review): bool
    {
        return $review->client_id === $user->getKey()
            && $review->created_at?->gt(now()->subHours(24));
    }

    public function delete(User $user, Review $review): bool
    {
        return false;
    }

    public function report(User $user, Review $review): bool
    {
        return $user->canActAsProfessional()
            && $review->professional?->user_id === $user->getKey();
    }
}
