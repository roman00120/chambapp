<?php

namespace Tests\Feature;

use App\Enums\JobStatus;
use App\Enums\QuoteStatus;
use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Models\User;
use App\Notifications\ChambappNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class QuotesAndNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_professional_can_replace_a_pending_quote_and_the_client_can_accept_only_one(): void
    {
        [$job, $client, $professional] = $this->jobFixture();

        $this->actingAs($professional)->post(route('job-quotes.store', $job), [
            'amount' => '800.00',
            'description' => 'Incluye mano de obra y materiales.',
        ])->assertRedirect();
        $first = $job->fresh()->quotes()->firstOrFail();

        $this->actingAs($professional)->post(route('job-quotes.store', $job), [
            'amount' => '900.00',
            'description' => 'Incluye materiales premium y limpieza.',
        ])->assertRedirect();
        $second = $job->fresh()->quotes()->latest('id')->firstOrFail();

        $this->assertSame(QuoteStatus::SUPERSEDED, $first->fresh()->status);
        $this->assertSame(QuoteStatus::PENDING, $second->status);
        $this->assertSame(JobStatus::ACCEPTED, $job->fresh()->status);

        $this->actingAs($client)->post(route('job-quotes.accept', $second))->assertRedirect();

        $this->assertSame(QuoteStatus::ACCEPTED, $second->fresh()->status);
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
        $this->assertSame('900.00', $job->fresh()->agreed_price);
        $this->assertCount(1, $professional->fresh()->notifications);
        $this->assertCount(3, $client->fresh()->notifications);

        $this->actingAs($professional)->post(route('job-requests.start', $job))->assertForbidden();
    }

    public function test_quote_access_is_authorized_and_contact_data_is_rejected(): void
    {
        [$job, $client, $professional] = $this->jobFixture();
        $otherProfessional = User::factory()->professional()->create();

        $this->actingAs($otherProfessional)->post(route('job-quotes.store', $job), [
            'amount' => '700',
            'description' => 'Propuesta ajena.',
        ])->assertForbidden();

        $this->actingAs($professional)->post(route('job-quotes.store', $job), [
            'amount' => '0',
            'description' => 'Escríbeme al 55 12 34 56 78',
        ])->assertSessionHasErrors(['amount', 'description']);
        $this->assertCount(0, $job->fresh()->quotes);
        $this->assertSame(JobStatus::PENDING, $job->fresh()->status);
        $this->assertSame('client@example.test', $client->email);
    }

    public function test_client_requests_cannot_contain_contact_information(): void
    {
        $professional = User::factory()->professional()->create();
        $profile = ProfessionalProfile::factory()->create(['user_id' => $professional->id]);
        $service = Service::factory()->create(['professional_id' => $profile->id]);
        $client = User::factory()->client()->create();
        $payload = [
            'title' => 'Necesito apoyo',
            'description' => 'Revisar una fuga en la cocina.',
            'requested_date' => now()->addDay()->format('Y-m-d\\TH:i'),
            'address' => 'Calle del Trabajo 10',
            'city' => 'Puebla',
            'state' => 'Puebla',
            'postal_code' => '72000',
        ];

        $this->actingAs($client)->post(route('job-requests.store', $service), array_merge($payload, [
            'title' => 'Llámame al 55 12 34 56 78',
        ]))->assertSessionHasErrors('title');
        $this->actingAs($client)->post(route('job-requests.store', $service), array_merge($payload, [
            'description' => 'Mi correo @ gmail . com para coordinar.',
        ]))->assertSessionHasErrors('description');

        $this->assertDatabaseCount('job_requests', 0);
    }

    public function test_quote_creation_is_rate_limited_per_professional(): void
    {
        [$job, , $professional] = $this->jobFixture();
        RateLimiter::clear((string) $professional->id);

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->actingAs($professional)->post(route('job-quotes.store', $job), [
                'amount' => 700 + $attempt,
                'description' => 'Propuesta número '.$attempt.'.',
            ])->assertRedirect();
        }

        $this->actingAs($professional)->post(route('job-quotes.store', $job), [
            'amount' => 900,
            'description' => 'Esta propuesta supera el límite.',
        ])->assertStatus(429);
        RateLimiter::clear((string) $professional->id);
    }

    public function test_client_can_reject_with_structured_reason_and_the_professional_can_send_another_quote(): void
    {
        [$job, $client, $professional] = $this->jobFixture();
        $this->actingAs($professional)->post(route('job-quotes.store', $job), [
            'amount' => '800',
            'description' => 'Incluye reparación y materiales.',
        ])->assertRedirect();
        $quote = $job->fresh()->quotes()->firstOrFail();

        $this->actingAs($client)->post(route('job-quotes.reject', $quote), [
            'reason' => 'other',
            'reason_detail' => 'Necesito ajustar el alcance.',
        ])->assertRedirect();

        $this->assertSame(QuoteStatus::REJECTED, $quote->fresh()->status);
        $this->assertSame('other: Necesito ajustar el alcance.', $quote->fresh()->rejection_reason);
        $this->assertSame(JobStatus::ACCEPTED, $job->fresh()->status);

        $this->actingAs($professional)->post(route('job-quotes.store', $job), [
            'amount' => '750',
            'description' => 'Nueva propuesta ajustada.',
        ])->assertRedirect();

        $this->assertSame(QuoteStatus::PENDING, $job->fresh()->quotes()->latest('id')->first()->status);
        $this->assertNotEmpty($professional->fresh()->notifications);
    }

    public function test_expired_quote_is_marked_and_cannot_be_accepted(): void
    {
        [$job, $client, $professional] = $this->jobFixture();
        $this->actingAs($professional)->post(route('job-quotes.store', $job), [
            'amount' => '800',
            'description' => 'Propuesta con vigencia limitada.',
        ])->assertRedirect();
        $quote = $job->fresh()->quotes()->firstOrFail();
        $quote->update(['expires_at' => now()->subMinute()]);

        $this->actingAs($client)->post(route('job-quotes.accept', $quote))->assertRedirect()->assertSessionHasErrors('quote');
        $this->assertSame(QuoteStatus::EXPIRED, $quote->fresh()->status);
        $this->assertSame(JobStatus::ACCEPTED, $job->fresh()->status);
    }

    public function test_notifications_are_private_unread_and_markable_as_read(): void
    {
        [$job, $client, $professional] = $this->jobFixture();
        $this->actingAs($professional)->post(route('job-quotes.store', $job), [
            'amount' => '800',
            'description' => 'Propuesta visible en notificaciones.',
        ])->assertRedirect();
        $notification = $client->fresh()->notifications()->firstOrFail();

        $this->actingAs($client)->get(route('notifications.index'))->assertOk()->assertSee('Recibiste una nueva cotización');
        $this->actingAs($client)->get(route('notifications.show', $notification))->assertRedirect(route('job-requests.show', $job));
        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($professional)->get(route('notifications.show', $notification))->assertForbidden();

        $client->notify(new ChambappNotification('test', 'Prueba', 'Texto', route('job-requests.show', $job)));
        $this->actingAs($client)->post(route('notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $client->fresh()->unreadNotifications()->count());
    }

    public function test_job_detail_hides_contact_information_and_full_address_before_payment(): void
    {
        [$job, $client, $professional] = $this->jobFixture([
            'address' => 'Calle Privada 1234',
            'city' => 'Puebla',
            'state' => 'Puebla',
            'postal_code' => '72000',
        ]);

        $this->actingAs($professional)->get(route('job-requests.show', $job))
            ->assertOk()
            ->assertDontSee($client->email)
            ->assertDontSee($client->phone)
            ->assertDontSee('Calle Privada 1234')
            ->assertSee('Puebla')
            ->assertSee('72000');

        $this->actingAs($client)->get(route('job-requests.show', $job))
            ->assertOk()
            ->assertDontSee($professional->email)
            ->assertDontSee($professional->phone);
    }

    private function jobFixture(array $attributes = []): array
    {
        $client = User::factory()->client()->create(['email' => 'client@example.test', 'phone' => '5512345678']);
        $professional = User::factory()->professional()->create(['email' => 'pro@example.test', 'phone' => '5587654321']);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $professional->id]);
        $service = Service::factory()->create(['professional_id' => $profile->id]);
        $job = JobRequest::factory()->create(array_merge([
            'client_id' => $client->id,
            'professional_id' => $profile->id,
            'service_id' => $service->id,
            'agreed_price' => null,
        ], $attributes));

        return [$job, $client, $professional];
    }
}
