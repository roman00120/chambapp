<?php

namespace App\Services;

use App\Enums\JobStatus;
use App\Models\JobRequest;
use App\Models\Review;
use App\Models\User;
use App\Notifications\ChambappNotification;
use DomainException;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function __construct(private readonly ProfessionalRatingService $ratings) {}

    public function create(JobRequest $jobRequest, User $client, int $rating, ?string $comment = null): Review
    {
        return DB::transaction(function () use ($jobRequest, $client, $rating, $comment): Review {
            $job = JobRequest::query()
                ->with(['professional.user', 'service'])
                ->lockForUpdate()
                ->findOrFail($jobRequest->getKey());

            if ($job->client_id !== $client->getKey() || $job->status !== JobStatus::COMPLETED) {
                throw new DomainException('Solo puedes calificar trabajos completados propios.');
            }
            if ($job->review()->exists()) {
                throw new DomainException('Este trabajo ya tiene una reseña.');
            }

            $review = $job->review()->create([
                'client_id' => $job->client_id,
                'professional_id' => $job->professional_id,
                'rating' => $rating,
                'comment' => $comment,
            ]);
            $this->ratings->recalculate($job->professional);
            $job->professional?->user?->notify(new ChambappNotification(
                'review_created',
                'Recibiste una nueva reseña',
                'Un cliente calificó tu servicio con '.$rating.' estrella'.($rating === 1 ? '' : 's').'.',
                route('professional.public-profile', $job->professional),
            ));

            return $review->fresh(['client', 'professional.user', 'jobRequest.service']);
        });
    }
}
