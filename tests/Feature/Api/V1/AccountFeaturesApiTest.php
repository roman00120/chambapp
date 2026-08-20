<?php

namespace Tests\Feature\Api\V1;

use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Models\User;
use App\Notifications\ChambappNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountFeaturesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_update_own_profile_and_crud_only_own_services(): void
    {
        $category = Category::factory()->create();
        $owner = ProfessionalProfile::factory()->create();
        $other = ProfessionalProfile::factory()->create();
        Sanctum::actingAs($owner->user);

        $this->patchJson('/api/v1/professional/profile', [
            'name' => 'Profesional API',
            'phone' => '5512345678',
            'bio' => 'Experiencia confiable para hogares y negocios.',
            'experience_years' => 8,
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            'postal_code' => '44100',
        ])->assertOk()->assertJsonPath('data.name', 'Profesional API');

        $created = $this->postJson('/api/v1/professional/services', [
            'category_id' => $category->id,
            'title' => 'Instalaciones eléctricas',
            'description' => 'Instalación y mantenimiento profesional para hogares y negocios.',
            'price_type' => 'fixed',
            'price' => '500.00',
        ])->assertCreated()->assertJsonPath('data.price', '500.00');
        $serviceId = $created->json('data.id');

        Sanctum::actingAs($other->user);
        $this->getJson('/api/v1/professional/services/'.$serviceId)->assertForbidden();
        $this->patchJson('/api/v1/professional/services/'.$serviceId, [
            'category_id' => $category->id,
            'title' => 'Intento ajeno',
            'description' => 'Descripción suficientemente larga para validar el intento.',
            'price_type' => 'fixed',
            'price' => '1.00',
        ])->assertForbidden();
    }

    public function test_favorites_require_client_role_and_are_idempotent(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        Sanctum::actingAs($client);

        $this->postJson('/api/v1/favorites/'.$professional->id)->assertCreated();
        $this->postJson('/api/v1/favorites/'.$professional->id)->assertOk();
        $this->assertDatabaseCount('favorites', 1);
        $this->getJson('/api/v1/favorites')->assertOk()->assertJsonPath('data.0.id', $professional->id);
        $this->deleteJson('/api/v1/favorites/'.$professional->id)->assertOk();
        $this->assertDatabaseCount('favorites', 0);

        Sanctum::actingAs($professional->user);
        $this->getJson('/api/v1/favorites')->assertForbidden();
    }

    public function test_notifications_and_payments_enforce_ownership_and_safe_serialization(): void
    {
        $client = User::factory()->client()->create();
        $other = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create([
            'mercadopago_access_token' => 'ultra-secret-token',
            'mercadopago_refresh_token' => 'ultra-secret-refresh',
        ]);
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => $professional->id]);
        $payment = Payment::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => PaymentStatus::APPROVED,
        ]);
        $client->notify(new ChambappNotification('test', 'Título', 'Mensaje', url('/')));
        $notification = $client->notifications()->firstOrFail();

        Sanctum::actingAs($other);
        $this->getJson('/api/v1/payments/'.$payment->id)->assertForbidden();
        $this->postJson('/api/v1/notifications/'.$notification->id.'/read')->assertForbidden();

        Sanctum::actingAs($client);
        $paymentResponse = $this->getJson('/api/v1/payments/'.$payment->id)->assertOk();
        $this->assertStringNotContainsString('ultra-secret', $paymentResponse->getContent());
        $this->assertStringNotContainsString('external_reference', $paymentResponse->getContent());
        $this->getJson('/api/v1/notifications')->assertOk()->assertJsonPath('data.0.id', $notification->id);
        $this->postJson('/api/v1/notifications/'.$notification->id.'/read')->assertOk();
        $this->assertNotNull($notification->fresh()->read_at);
    }
}
