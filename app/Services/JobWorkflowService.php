<?php

namespace App\Services;

use App\Enums\AvailabilityStatus;
use App\Enums\JobDisputeStatus;
use App\Enums\JobStatus;
use App\Enums\QuoteStatus;
use App\Models\JobDispute;
use App\Models\JobQuote;
use App\Models\JobRequest;
use App\Models\User;
use App\Notifications\ChambappNotification;
use DomainException;
use Illuminate\Support\Facades\DB;

class JobWorkflowService
{
    public function __construct(
        private readonly PaymentCalculationService $paymentCalculation,
        private readonly ProfessionalIdentityVerificationService $identityVerification,
    ) {}

    public function reject(JobRequest $jobRequest): JobRequest
    {
        $job = $this->transition($jobRequest, JobStatus::PENDING, JobStatus::REJECTED, ['cancelled_at' => null]);
        $job->client?->notify(new \App\Notifications\JobCancelledNotification(
            $job,
            'El profesional no podrá atender esta solicitud.',
            route('job-requests.show', $job)
        ));

        return $job;
    }

    public function start(JobRequest $jobRequest): JobRequest
    {
        $this->identityVerification->ensureProfessionalCanAcceptJobs($jobRequest->professional);

        return $this->transition($jobRequest, [JobStatus::PAID, JobStatus::ARRIVED], JobStatus::IN_PROGRESS, ['started_at' => now()]);
    }

    public function onTheWay(JobRequest $jobRequest): JobRequest
    {
        $this->identityVerification->ensureProfessionalCanAcceptJobs($jobRequest->professional);

        $job = $this->transition($jobRequest, JobStatus::PAID, JobStatus::ON_THE_WAY, ['on_the_way_at' => now()]);
        $job->client?->notify(new \App\Notifications\JobStatusUpdatedNotification(
            $job,
            'El profesional está en camino',
            'Tu profesional va rumbo a la ubicación acordada.',
            'En camino',
            route('job-requests.show', $job),
            'job_on_the_way'
        ));

        return $job;
    }

    public function arrive(JobRequest $jobRequest): JobRequest
    {
        $this->identityVerification->ensureProfessionalCanAcceptJobs($jobRequest->professional);

        $job = $this->transition($jobRequest, JobStatus::ON_THE_WAY, JobStatus::ARRIVED, ['arrived_at' => now()]);
        $job->client?->notify(new \App\Notifications\JobStatusUpdatedNotification(
            $job,
            'El profesional ha llegado',
            'Tu profesional ha llegado a la ubicación.',
            'Llegó',
            route('job-requests.show', $job),
            'job_arrived'
        ));

        return $job;
    }

    public function finish(JobRequest $jobRequest): JobRequest
    {
        $job = $this->transition($jobRequest, JobStatus::IN_PROGRESS, JobStatus::AWAITING_CONFIRMATION, [
            'finished_at' => now(),
            'completion_code' => (string) random_int(100000, 999999),
            'completion_code_expires_at' => now()->addHours(24),
        ]);
        $job->client?->notify(new ChambappNotification(
            'job_awaiting_confirmation',
            'El profesional indicó que terminó el trabajo',
            'Revisa el trabajo y confirma si fue realizado correctamente.',
            route('job-requests.show', $job),
        ));

        return $job;
    }

    public function confirmCompletion(JobRequest $jobRequest, string $completionCode): JobRequest
    {
        return DB::transaction(function () use ($jobRequest, $completionCode): JobRequest {
            $job = $this->locked($jobRequest);
            $this->ensureStatus($job, JobStatus::AWAITING_CONFIRMATION, JobStatus::COMPLETED);
            if (! $job->completion_code || $job->completion_code_expires_at?->isPast() || ! hash_equals((string) $job->completion_code, $completionCode)) {
                throw new DomainException('El código de finalización no es válido o ya expiró.');
            }
            $job->forceFill([
                'status' => JobStatus::COMPLETED,
                'completed_at' => now(),
                'completion_confirmed_at' => now(),
            ])->save();

            $profile = $job->professional()->lockForUpdate()->firstOrFail();
            $profile->forceFill([
                'availability_status' => AvailabilityStatus::AVAILABLE,
                'total_completed_jobs' => JobRequest::query()
                    ->where('professional_id', $profile->getKey())
                    ->where('status', JobStatus::COMPLETED->value)
                    ->count(),
            ])->save();
            $job = $job->fresh(['client', 'professional.user', 'service']);
            $job->client?->notify(new \App\Notifications\JobCompletedNotification($job));
            $job->professional?->user?->notify(new \App\Notifications\JobStatusUpdatedNotification(
                $job,
                'Trabajo completado',
                'El cliente confirmó que el trabajo terminó correctamente.',
                'Completado',
                route('job-requests.show', $job),
                'job_completed'
            ));

            return $job;
        });
    }

    public function openDispute(JobRequest $jobRequest, User $client, string $reason, ?string $description = null): JobDispute
    {
        return DB::transaction(function () use ($jobRequest, $client, $reason, $description): JobDispute {
            $job = $this->locked($jobRequest);
            if ($job->client_id !== $client->getKey()) {
                throw new DomainException('Solo el cliente propietario puede reportar este trabajo.');
            }
            $this->ensureStatus($job, JobStatus::AWAITING_CONFIRMATION, JobStatus::DISPUTED);
            $dispute = $job->dispute()->create([
                'opened_by' => $client->getKey(),
                'reason' => $reason,
                'description' => $description,
                'status' => JobDisputeStatus::OPEN,
            ]);
            $job->forceFill(['status' => JobStatus::DISPUTED])->save();
            $job->professional?->user?->notify(new ChambappNotification(
                'job_disputed',
                'Se reportó un problema con el trabajo',
                'El caso fue enviado a revisión por Chambapp.',
                route('job-requests.show', $job),
            ));

            return $dispute->fresh(['jobRequest']);
        });
    }

    public function cancel(JobRequest $jobRequest, ?string $reason = null, ?User $actor = null): JobRequest
    {
        $job = $this->transition($jobRequest, [JobStatus::PENDING, JobStatus::ACCEPTED], JobStatus::CANCELLED, [
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
        $recipient = $actor?->getKey() === $job->client_id ? $job->professional?->user : $job->client;
        $recipient?->notify(new \App\Notifications\JobCancelledNotification(
            $job,
            $reason ?? 'Una solicitud de trabajo fue cancelada.',
            route('job-requests.show', $job)
        ));

        return $job;
    }

    public function ensureActor(User $user, JobRequest $jobRequest): void
    {
        abort_unless($user->can('view', $jobRequest), 403);
    }

    public function createQuote(JobRequest $jobRequest, User $professional, string $amount, string $description): JobQuote
    {
        $this->identityVerification->ensureProfessionalCanAcceptJobs($professional);

        return DB::transaction(function () use ($jobRequest, $professional, $amount, $description): JobQuote {
            $job = $this->locked($jobRequest);
            if ($job->professional?->user_id !== $professional->getKey()) {
                throw new DomainException('No puedes cotizar este trabajo.');
            }
            if (! in_array($job->status, [JobStatus::PENDING, JobStatus::ACCEPTED, JobStatus::MATCHED, JobStatus::AWAITING_QUOTE], true)) {
                throw new DomainException('Este trabajo ya no acepta nuevas cotizaciones.');
            }

            $job->quotes()->where('status', QuoteStatus::PENDING->value)->update(['status' => QuoteStatus::SUPERSEDED->value]);
            if ($job->status === JobStatus::PENDING) {
                $job->forceFill(['status' => JobStatus::ACCEPTED, 'accepted_at' => now()])->save();
            } elseif ($job->status === JobStatus::MATCHED) {
                $job->forceFill(['status' => JobStatus::AWAITING_QUOTE])->save();
            }

            $quote = $job->quotes()->create([
                'professional_id' => $job->professional_id,
                'amount' => $this->paymentCalculation->normalize($amount),
                'description' => $description,
                'status' => QuoteStatus::PENDING,
                'expires_at' => now()->addHours(48),
            ]);
            $quote->load(['jobRequest.service', 'jobRequest.client', 'professional.user']);
            $breakdown = $this->paymentCalculation->calculateJob((string) $quote->amount);
            $job->client?->notify(new \App\Notifications\QuoteReceivedNotification($quote, $breakdown));

            return $quote;
        });
    }

    public function acceptQuote(JobQuote $jobQuote, User $client): JobQuote
    {
        if ($jobQuote->isExpired()) {
            $jobQuote->markExpiredIfNeeded();
            throw new DomainException('Esta cotización ya expiró.');
        }

        $this->identityVerification->ensureProfessionalCanAcceptJobs($jobQuote->jobRequest?->professional);

        return DB::transaction(function () use ($jobQuote, $client): JobQuote {
            $quote = JobQuote::query()->with(['jobRequest.client', 'jobRequest.professional.user', 'jobRequest.service'])->lockForUpdate()->findOrFail($jobQuote->getKey());
            $job = JobRequest::query()->with(['client', 'professional.user', 'service'])->lockForUpdate()->findOrFail($quote->job_request_id);
            if ($job->client_id !== $client->getKey()) {
                throw new DomainException('No puedes aceptar esta cotización.');
            }
            if ($quote->status !== QuoteStatus::PENDING) {
                throw new DomainException('Esta cotización ya no está disponible.');
            }
            if (! in_array($job->status, [JobStatus::ACCEPTED, JobStatus::AWAITING_QUOTE], true)) {
                throw new DomainException('El trabajo ya no está disponible para aceptar esta cotización.');
            }

            $job->quotes()->where('id', '!=', $quote->getKey())->where('status', QuoteStatus::PENDING->value)->update(['status' => QuoteStatus::SUPERSEDED->value]);
            $quote->forceFill(['status' => QuoteStatus::ACCEPTED, 'accepted_at' => now()])->save();
            $money = $this->paymentCalculation->calculateJob((string) $quote->amount);
            $job->forceFill([
                'status' => JobStatus::AWAITING_PAYMENT,
                'agreed_price' => $money->baseAmount,
                'economic_model_version' => $money->economicModelVersion,
                'base_amount' => $money->baseAmount,
                'client_service_fee_percent' => $money->clientServiceFeePercent,
                'client_service_fee' => $money->clientServiceFee,
                'professional_commission_percent' => $money->professionalCommissionPercent,
                'professional_commission' => $money->professionalCommission,
                'customer_total' => $money->customerTotal,
                'platform_gross_fee' => $money->platformGrossFee,
                'professional_amount_before_external_costs' => $money->professionalAmountBeforeExternalCosts,
            ])->save();
            $quote->professional?->user?->notify(new \App\Notifications\QuoteAcceptedNotification($quote));
            $client->notify(new ChambappNotification(
                'job_awaiting_payment',
                'Cotización aceptada: pendiente de pago',
                'El siguiente paso es pagar en Chambapp para formalizar la contratación.',
                route('job-requests.show', $job),
            ));

            return $quote->fresh(['jobRequest', 'professional.user']);
        });
    }

    public function rejectQuote(JobQuote $jobQuote, User $client, ?string $reason = null): JobQuote
    {
        if ($jobQuote->isExpired()) {
            $jobQuote->markExpiredIfNeeded();
            throw new DomainException('Esta cotización ya expiró.');
        }

        return DB::transaction(function () use ($jobQuote, $client, $reason): JobQuote {
            $quote = JobQuote::query()->with(['jobRequest.client', 'jobRequest.service', 'professional.user'])->lockForUpdate()->findOrFail($jobQuote->getKey());
            if ($quote->jobRequest?->client_id !== $client->getKey()) {
                throw new DomainException('No puedes rechazar esta cotización.');
            }
            if ($quote->status !== QuoteStatus::PENDING) {
                throw new DomainException('Esta cotización ya no está disponible.');
            }

            $quote->forceFill([
                'status' => QuoteStatus::REJECTED,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ])->save();
            $quote->professional?->user?->notify(new ChambappNotification(
                'quote_rejected',
                'El cliente rechazó tu cotización',
                'Puedes revisar la solicitud y enviar una nueva propuesta estructurada.',
                route('job-requests.show', $quote->jobRequest),
            ));

            return $quote->fresh(['jobRequest', 'professional.user']);
        });
    }

    private function transition(JobRequest $jobRequest, JobStatus|array $from, JobStatus $to, array $attributes): JobRequest
    {
        return DB::transaction(function () use ($jobRequest, $from, $to, $attributes): JobRequest {
            $job = $this->locked($jobRequest);
            $this->ensureStatus($job, $from, $to);
            $job->forceFill(array_merge($attributes, ['status' => $to]))->save();

            return $job->fresh(['client', 'professional.user', 'service']);
        });
    }

    private function locked(JobRequest $jobRequest): JobRequest
    {
        return JobRequest::query()
            ->with(['client', 'professional.user', 'service'])
            ->lockForUpdate()
            ->findOrFail($jobRequest->getKey());
    }

    private function ensureStatus(JobRequest $jobRequest, JobStatus|array $from, JobStatus $to): void
    {
        $allowed = is_array($from) ? $from : [$from];
        if (! in_array($jobRequest->status, $allowed, true)) {
            throw new DomainException("No se puede cambiar de {$jobRequest->status->value} a {$to->value}.");
        }
    }
}
