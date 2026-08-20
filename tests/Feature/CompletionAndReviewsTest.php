<?php

namespace Tests\Feature;

use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompletionAndReviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_work_requires_professional_finish_then_client_confirmation_and_updates_counts(): void
    {
        [$job, $client, $professional] = $this->job(JobStatus::PAID);
        $payment = Payment::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => PaymentStatus::APPROVED,
            'gross_amount' => '1000.00',
            'platform_fee_percent' => '15.00',
            'platform_fee' => '150.00',
            'professional_amount' => '850.00',
        ]);

        $this->actingAs($professional->user)->post(route('job-requests.start', $job))->assertRedirect();
        $this->assertSame(JobStatus::IN_PROGRESS, $job->fresh()->status);
        $this->actingAs($professional->user)->post(route('job-requests.finish', $job))->assertRedirect();
        $this->assertSame(JobStatus::AWAITING_CONFIRMATION, $job->fresh()->status);
        $this->assertNotNull($job->fresh()->finished_at);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $client->id]);

        $this->actingAs($client)->post(route('job-requests.complete', $job))->assertRedirect();
        $this->assertSame(JobStatus::COMPLETED, $job->fresh()->status);
        $this->assertNotNull($job->fresh()->completed_at);
        $this->assertSame(1, $professional->fresh()->total_completed_jobs);
        $this->assertSame('1000.00', $payment->fresh()->gross_amount);
        $this->assertSame('15.00', $payment->fresh()->platform_fee_percent);
        $this->assertSame('150.00', $payment->fresh()->platform_fee);
        $this->assertSame('850.00', $payment->fresh()->professional_amount);

        $this->actingAs($client)->post(route('job-requests.complete', $job))->assertForbidden();
        $this->assertSame(1, $professional->fresh()->total_completed_jobs);
    }

    public function test_client_can_open_basic_dispute_without_payment_change_or_refund(): void
    {
        [$job, $client, $professional] = $this->job(JobStatus::AWAITING_CONFIRMATION);
        $payment = Payment::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => PaymentStatus::APPROVED,
            'gross_amount' => '800.00',
            'platform_fee_percent' => '15.00',
            'platform_fee' => '120.00',
            'professional_amount' => '680.00',
        ]);

        $this->actingAs($client)->post(route('job-requests.dispute', $job), [
            'reason' => 'incomplete_work',
            'description' => 'Faltó terminar una parte del servicio.',
        ])->assertRedirect();

        $this->assertSame(JobStatus::DISPUTED, $job->fresh()->status);
        $this->assertDatabaseHas('job_disputes', [
            'job_request_id' => $job->id,
            'opened_by' => $client->id,
            'reason' => 'incomplete_work',
            'status' => 'open',
        ]);
        $this->assertSame('approved', $payment->fresh()->status->value);
        $this->assertSame('800.00', $payment->fresh()->gross_amount);
        $this->assertSame(0, $professional->fresh()->total_completed_jobs);
    }

    public function test_completed_job_allows_only_owner_to_publish_one_review_and_updates_reputation(): void
    {
        [$job, $client, $professional] = $this->job(JobStatus::COMPLETED);
        $otherClient = User::factory()->client()->create();

        $this->actingAs($professional->user)->get(route('reviews.create', $job))->assertForbidden();
        $this->actingAs($otherClient)->post(route('reviews.store', $job), ['rating' => 5])->assertForbidden();
        $this->actingAs($client)->post(route('reviews.store', $job), [
            'rating' => 5,
            'comment' => 'Excelente servicio, llegó puntual y realizó muy buen trabajo.',
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'rating' => 5,
        ]);
        $profile = $professional->fresh();
        $this->assertSame(1, $profile->total_reviews);
        $this->assertSame('5.00', $profile->average_rating);
        $this->actingAs($client)->post(route('reviews.store', $job), ['rating' => 4])->assertForbidden();
    }

    public function test_review_validates_integer_range_and_contact_guard_and_escapes_public_comment(): void
    {
        [$job, $client, $professional] = $this->job(JobStatus::COMPLETED);
        $this->actingAs($client)->post(route('reviews.store', $job), ['rating' => 6])->assertSessionHasErrors('rating');
        $this->actingAs($client)->post(route('reviews.store', $job), ['rating' => 0])->assertSessionHasErrors('rating');
        $this->actingAs($client)->post(route('reviews.store', $job), ['rating' => 5, 'comment' => 'Mi número es 5512345678'])->assertSessionHasErrors('comment');
        $this->actingAs($client)->post(route('reviews.store', $job), ['rating' => 4, 'comment' => '<script>alert("x")</script>'])->assertRedirect();

        $response = $this->get(route('professional.public-profile', $professional));
        $response->assertOk()->assertSee('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', false)
            ->assertDontSee($client->email)
            ->assertDontSee($client->phone)
            ->assertSee('Trabajo realizado en Chambapp');
    }

    public function test_reputation_average_is_rebuilt_from_reviews_and_marketplace_orders_real_rating(): void
    {
        [$firstJob, $client, $professional] = $this->job(JobStatus::COMPLETED);
        $this->actingAs($client)->post(route('reviews.store', $firstJob), ['rating' => 5])->assertRedirect();
        foreach ([4, 3, 5] as $rating) {
            [$job] = $this->job(JobStatus::COMPLETED, $client, $professional);
            $this->actingAs($client)->post(route('reviews.store', $job), ['rating' => $rating])->assertRedirect();
        }

        $profile = $professional->fresh();
        $this->assertSame(4, $profile->total_reviews);
        $this->assertSame('4.25', $profile->average_rating);
        $this->get(route('professional.public-profile', $profile))->assertOk()->assertSee('4.3 (4)');
    }

    public function test_professional_can_report_review_without_hiding_or_deleting_it(): void
    {
        [$job, $client, $professional] = $this->job(JobStatus::COMPLETED);
        $review = Review::create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'rating' => 2,
            'comment' => 'Servicio recibido.',
        ]);

        $this->actingAs($professional->user)->post(route('reviews.report', $review), [
            'reason' => 'unrelated',
            'description' => 'Este comentario no corresponde al servicio.',
        ])->assertRedirect();
        $this->assertDatabaseHas('reports', [
            'reportable_type' => Review::class,
            'reportable_id' => $review->id,
            'reporter_id' => $professional->user_id,
            'reason' => 'unrelated',
        ]);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    private function job(JobStatus $status, ?User $client = null, ?ProfessionalProfile $professional = null): array
    {
        $client ??= User::factory()->client()->create();
        $professional ??= ProfessionalProfile::factory()->create();
        $service = Service::factory()->create(['professional_id' => $professional->id]);
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'service_id' => $service->id,
            'status' => $status,
            'accepted_at' => $status !== JobStatus::PENDING ? now()->subDays(3) : null,
            'started_at' => in_array($status, [JobStatus::IN_PROGRESS, JobStatus::AWAITING_CONFIRMATION, JobStatus::COMPLETED], true) ? now()->subDay() : null,
            'finished_at' => in_array($status, [JobStatus::AWAITING_CONFIRMATION, JobStatus::COMPLETED], true) ? now()->subHours(3) : null,
            'completed_at' => $status === JobStatus::COMPLETED ? now()->subHour() : null,
        ]);

        return [$job, $client, $professional];
    }
}
