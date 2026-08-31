<?php

namespace App\Policies;

use App\Enums\JobStatus;
use App\Enums\PaymentKind;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Enums\UserRole;
use App\Models\JobRequest;
use App\Models\User;

class JobRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canActAsClient();
    }

    public function create(User $user, JobRequest $jobRequest): bool
    {
        return $user->canActAsProfessional()
            && $jobRequest->professional?->user_id === $user->getKey()
            && $jobRequest->client_id !== $user->getKey()
            && in_array($jobRequest->status, [JobStatus::PENDING, JobStatus::ACCEPTED, JobStatus::MATCHED, JobStatus::AWAITING_QUOTE], true);
    }

    public function view(User $user, JobRequest $jobRequest): bool
    {
        return $this->isClientParticipant($user, $jobRequest) || $this->isProfessionalParticipant($user, $jobRequest);
    }

    public function update(User $user, JobRequest $jobRequest): bool
    {
        return $this->view($user, $jobRequest)
            && in_array($jobRequest->status, [JobStatus::PENDING, JobStatus::ACCEPTED], true);
    }

    public function reject(User $user, JobRequest $jobRequest): bool
    {
        return $this->isProfessionalParticipant($user, $jobRequest)
            && $jobRequest->status === JobStatus::PENDING;
    }

    public function acceptDirect(User $user, JobRequest $jobRequest): bool
    {
        return $this->isProfessionalParticipant($user, $jobRequest)
            && $jobRequest->status === JobStatus::PENDING
            && $jobRequest->service_id !== null;
    }

    public function start(User $user, JobRequest $jobRequest): bool
    {
        return $this->isProfessionalParticipant($user, $jobRequest)
            && in_array($jobRequest->status, [JobStatus::PAID, JobStatus::ARRIVED], true);
    }

    public function onTheWay(User $user, JobRequest $jobRequest): bool
    {
        return $this->isProfessionalParticipant($user, $jobRequest)
            && $jobRequest->status === JobStatus::PAID;
    }

    public function arrive(User $user, JobRequest $jobRequest): bool
    {
        return $this->isProfessionalParticipant($user, $jobRequest)
            && $jobRequest->status === JobStatus::ON_THE_WAY;
    }

    public function paymentSummary(User $user, JobRequest $jobRequest): bool
    {
        return $this->isClientParticipant($user, $jobRequest)
            && $jobRequest->status === JobStatus::AWAITING_PAYMENT
            && ($jobRequest->service_id !== null || $jobRequest->quotes()->where('status', QuoteStatus::ACCEPTED->value)->exists());
    }

    public function pay(User $user, JobRequest $jobRequest): bool
    {
        return $this->paymentSummary($user, $jobRequest)
            && $jobRequest->professional?->isMercadoPagoConnected()
            && ! $jobRequest->payments()
                ->where('kind', PaymentKind::JOB->value)
                ->where('status', PaymentStatus::APPROVED->value)
                ->exists();
    }

    public function complete(User $user, JobRequest $jobRequest): bool
    {
        return $this->isProfessionalParticipant($user, $jobRequest)
            && $jobRequest->status === JobStatus::AWAITING_CONFIRMATION;
    }

    public function dispute(User $user, JobRequest $jobRequest): bool
    {
        return $this->isClientParticipant($user, $jobRequest)
            && $jobRequest->status === JobStatus::AWAITING_CONFIRMATION
            && ! $jobRequest->dispute()->exists();
    }

    public function finish(User $user, JobRequest $jobRequest): bool
    {
        return $this->isProfessionalParticipant($user, $jobRequest)
            && $jobRequest->status === JobStatus::IN_PROGRESS;
    }

    public function cancel(User $user, JobRequest $jobRequest): bool
    {
        return $jobRequest->status !== JobStatus::IN_PROGRESS
            && in_array($jobRequest->status, [JobStatus::PENDING, JobStatus::ACCEPTED, JobStatus::SEARCHING], true)
            && ($this->isClientParticipant($user, $jobRequest) || $this->isProfessionalParticipant($user, $jobRequest));
    }

    private function isClientParticipant(User $user, JobRequest $jobRequest): bool
    {
        return $jobRequest->client_id === $user->getKey()
            && $jobRequest->professional?->user_id !== $user->getKey();
    }

    private function isProfessionalParticipant(User $user, JobRequest $jobRequest): bool
    {
        return $user->canActAsProfessional()
            && $jobRequest->professional?->user_id === $user->getKey()
            && $jobRequest->client_id !== $user->getKey();
    }
}
