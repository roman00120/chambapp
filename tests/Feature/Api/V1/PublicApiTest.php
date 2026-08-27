<?php

namespace Tests\Feature\Api\V1;

use App\Enums\VerificationStatus;
use App\Enums\IdentityVerificationStatus;
use App\Models\Category;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_and_active_categories_are_public(): void
    {
        Category::factory()->create(['name' => 'Electricidad', 'slug' => 'electricidad', 'is_active' => true]);
        Category::factory()->create(['name' => 'Oculta', 'slug' => 'oculta', 'is_active' => false]);

        $this->getJson('/api/v1/health')->assertOk()->assertExactJson(['status' => 'ok', 'api_version' => 'v1']);
        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'electricidad'])
            ->assertJsonMissing(['slug' => 'oculta']);
    }

    public function test_services_support_search_and_filters_without_leaking_private_data(): void
    {
        $electricity = Category::factory()->create(['slug' => 'electricidad']);
        $plumbing = Category::factory()->create(['slug' => 'plomeria']);
        $professional = ProfessionalProfile::factory()->create([
            'verification_status' => VerificationStatus::VERIFIED,
            'city' => 'Guadalajara',
            'mercadopago_access_token' => 'secret-token',
        ]);
        $professional->identityVerification()->create([
            'status' => IdentityVerificationStatus::VERIFIED,
            'verified_at' => now(),
        ]);
        Service::factory()->create([
            'professional_id' => $professional->id,
            'category_id' => $electricity->id,
            'title' => 'Instalación eléctrica residencial',
        ]);
        Service::factory()->create([
            'professional_id' => $professional->id,
            'category_id' => $plumbing->id,
            'title' => 'Reparación de tuberías',
        ]);

        $response = $this->getJson('/api/v1/services?search=eléctrica&category=electricidad&city=Guadalajara&verified=1');
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Instalación eléctrica residencial');
        $json = $response->getContent();
        $this->assertStringNotContainsString('secret-token', $json);
        $this->assertStringNotContainsString('mercadopago_access_token', $json);
        $this->assertStringNotContainsString('password', $json);
    }

    public function test_professional_detail_exposes_only_public_location_and_content(): void
    {
        $professional = ProfessionalProfile::factory()->verifiedIdentity()->create([
            'verification_status' => VerificationStatus::VERIFIED,
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            'latitude' => '20.6736000',
            'longitude' => '-103.3440000',
            'last_latitude' => '20.6736000',
            'last_longitude' => '-103.3440000',
        ]);

        $response = $this->getJson('/api/v1/professionals/'.$professional->id)->assertOk();
        $response->assertJsonPath('data.city', 'Guadalajara')
            ->assertJsonMissingPath('data.latitude')
            ->assertJsonMissingPath('data.longitude')
            ->assertJsonMissingPath('data.phone')
            ->assertJsonMissingPath('data.email');
    }
}
