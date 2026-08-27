<?php

namespace Tests\Feature\Api\V1;

use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use App\Models\Review;
use App\Models\User;
use App\Notifications\ChambappNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class M7ContractApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_detail_and_histories_expose_review_resource_or_null_without_private_data(): void
    {
        $client = User::factory()->client()->create([
            'name' => 'Ana Privada',
            'email' => 'ana-private@example.test',
            'phone' => '5512345678',
        ]);
        $professional = ProfessionalProfile::factory()->verifiedIdentity()->create();
        $job = JobRequest::factory()->completed()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
        ]);

        Sanctum::actingAs($client);
        $this->getJson('/api/v1/jobs/'.$job->id)
            ->assertOk()
            ->assertJsonPath('data.review', null);
        $this->getJson('/api/v1/jobs')
            ->assertOk()
            ->assertJsonPath('data.0.review', null);

        $created = $this->postJson('/api/v1/jobs/'.$job->id.'/review', [
            'rating' => 5,
            'comment' => 'Trabajo realizado correctamente.',
        ])->assertCreated();
        $reviewId = $created->json('data.id');

        foreach (['/api/v1/jobs/'.$job->id, '/api/v1/jobs'] as $url) {
            $response = $this->getJson($url)
                ->assertOk()
                ->assertJsonPath(str_contains($url, '/jobs/') ? 'data.review.id' : 'data.0.review.id', $reviewId);
            $this->assertStringNotContainsString('ana-private@example.test', $response->getContent());
            $this->assertStringNotContainsString('5512345678', $response->getContent());
            $this->assertStringNotContainsString('moderation_reason', $response->getContent());
            $this->assertStringNotContainsString('payment_id', $response->getContent());
        }

        Sanctum::actingAs($professional->user);
        $professionalHistory = $this->getJson('/api/v1/professional/jobs')
            ->assertOk()
            ->assertJsonPath('data.0.review.id', $reviewId)
            ->assertJsonPath('data.0.review.client_name', 'Ana P.');
        $this->assertStringNotContainsString('ana-private@example.test', $professionalHistory->getContent());
        $this->assertStringNotContainsString('5512345678', $professionalHistory->getContent());
    }

    public function test_job_review_isolated_from_unrelated_users_and_duplicate_stays_rejected(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->verifiedIdentity()->create();
        $job = JobRequest::factory()->completed()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
        ]);
        Review::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
        ]);

        // A duplicate review is rejected by the ReviewPolicy (review already exists → forbidden).
        Sanctum::actingAs($client);
        $this->postJson('/api/v1/jobs/'.$job->id.'/review', ['rating' => 4])->assertForbidden();

        $otherClient = User::factory()->client()->create();
        Sanctum::actingAs($otherClient);
        $this->postJson('/api/v1/jobs/'.$job->id.'/review', ['rating' => 4])->assertForbidden();
    }

    public function test_hidden_review_remains_excluded_from_public_review_listing(): void
    {
        $professional = ProfessionalProfile::factory()->verifiedIdentity()->create();
        Review::factory()->create([
            'professional_id' => $professional->id,
            'is_hidden' => true,
        ]);
        Review::factory()->create([
            'professional_id' => $professional->id,
            'is_hidden' => false,
        ]);

        $this->getJson('/api/v1/professionals/'.$professional->id.'/reviews')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_notifications_use_body_with_historical_fallback_and_safe_destinations(): void
    {
        $client = User::factory()->client()->create();
        $job = JobRequest::factory()->create(['client_id' => $client->id]);
        $professional = ProfessionalProfile::factory()->verifiedIdentity()->create();
        $client->notify(new ChambappNotification(
            'payment_approved',
            'Pago aprobado',
            'El pago fue confirmado.',
            route('job-requests.show', $job),
        ));
        $jobNotification = $client->notifications()->latest()->firstOrFail();
        $client->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ChambappNotification::class,
            'data' => [
                'type' => 'historical',
                'title' => 'Histórica',
                'message' => 'Mensaje anterior compatible.',
                'url' => route('professional.public-profile', $professional),
            ],
        ]);
        $client->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ChambappNotification::class,
            'data' => [
                'type' => 'unknown_future_type',
                'title' => 'Genérica',
                'body' => 'Tipo desconocido visible.',
                'url' => 'https://evil.example/steal',
                'secret' => 'never-expose-this',
                'email' => 'private@example.test',
            ],
        ]);

        Sanctum::actingAs($client);
        $response = $this->getJson('/api/v1/notifications')->assertOk();
        $items = collect($response->json('data'));
        $jobItem = $items->firstWhere('id', $jobNotification->id);
        $this->assertSame('El pago fue confirmado.', $jobItem['message']);
        $this->assertSame(['kind' => 'job', 'id' => $job->id], $jobItem['destination']);
        $historical = $items->firstWhere('type', 'historical');
        $this->assertSame('Mensaje anterior compatible.', $historical['message']);
        $this->assertSame(['kind' => 'professional', 'id' => $professional->id], $historical['destination']);
        $unknown = $items->firstWhere('type', 'unknown_future_type');
        $this->assertSame('Tipo desconocido visible.', $unknown['message']);
        $this->assertNull($unknown['destination']);
        $this->assertStringNotContainsString('evil.example', $response->getContent());
        $this->assertStringNotContainsString('never-expose-this', $response->getContent());
        $this->assertStringNotContainsString('private@example.test', $response->getContent());
    }

    public function test_unread_count_is_global_decreases_on_read_and_reaches_zero_on_read_all(): void
    {
        $client = User::factory()->client()->create();
        foreach (range(1, 25) as $index) {
            $client->notify(new ChambappNotification(
                'test',
                'Notificación '.$index,
                'Mensaje '.$index,
                url('/'),
            ));
        }
        $first = $client->notifications()->firstOrFail();
        Sanctum::actingAs($client);

        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.unread_count', 25);
        $this->postJson('/api/v1/notifications/'.$first->id.'/read')->assertOk();
        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('meta.unread_count', 24);
        $this->postJson('/api/v1/notifications/read-all')->assertOk();
        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('meta.unread_count', 0);
    }

    public function test_notification_listing_is_strictly_isolated_by_authenticated_user(): void
    {
        $userA = User::factory()->client()->create();
        $userB = User::factory()->client()->create();
        $userA->notify(new ChambappNotification('a', 'Solo A', 'Privada A', url('/')));
        $userB->notify(new ChambappNotification('b', 'Solo B', 'Privada B', url('/')));

        Sanctum::actingAs($userA);
        $response = $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Solo A');
        $this->assertStringNotContainsString('Solo B', $response->getContent());
    }
}
