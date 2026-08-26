<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfessionalPhotoResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_with_photo_returns_valid_urls_and_renders_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'Carlos Mendoza',
            'role' => UserRole::PROFESSIONAL,
            'status' => UserStatus::ACTIVE,
        ]);

        $photo = UploadedFile::fake()->image('carlos.jpg', 400, 400);
        $path = $photo->store('profiles', 'public');

        $profile = ProfessionalProfile::factory()->create([
            'user_id' => $user->id,
            'profile_photo' => $path,
        ]);

        $expectedUrl = Storage::disk('public')->url($path);

        $this->assertEquals($expectedUrl, $profile->profilePhotoUrl());
        $this->assertEquals($expectedUrl, $user->profilePhotoUrl());

        // Test API response
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/auth/me');
        $response->assertOk();
        $response->assertJsonPath('data.avatar', $expectedUrl);
        $response->assertJsonPath('data.profile_photo_url', $expectedUrl);

        $proResponse = $this->getJson("/api/v1/professionals/{$profile->id}");
        $proResponse->assertOk();
        $proResponse->assertJsonPath('data.avatar', $expectedUrl);
        $proResponse->assertJsonPath('data.profile_photo_url', $expectedUrl);

        // Test Blade rendering
        $blade = Blade::render('<x-ui.avatar :user="$user" size="lg" />', ['user' => $user->fresh('professionalProfile')]);
        $this->assertStringContainsString('src="'.$expectedUrl.'"', $blade);
        $this->assertStringContainsString('alt="Foto de Carlos Mendoza"', $blade);

        $cardBlade = Blade::render('<x-professional-card :professional="$profile" />', ['profile' => $profile->fresh('user')]);
        $this->assertStringContainsString('src="'.$expectedUrl.'"', $cardBlade);
    }

    public function test_professional_without_photo_renders_fallback_initials(): void
    {
        $user = User::factory()->create([
            'name' => 'Ana Ramirez',
            'role' => UserRole::PROFESSIONAL,
            'status' => UserStatus::ACTIVE,
            'avatar_url' => null,
        ]);

        $profile = ProfessionalProfile::factory()->create([
            'user_id' => $user->id,
            'profile_photo' => null,
        ]);

        $this->assertNull($profile->profilePhotoUrl());
        $this->assertNull($user->profilePhotoUrl());

        $blade = Blade::render('<x-ui.avatar :user="$user" size="md" />', ['user' => $user->fresh('professionalProfile')]);
        $this->assertStringContainsString('AR', $blade);
        $this->assertStringNotContainsString('<img', $blade);
    }

    public function test_two_professionals_have_distinct_photos_without_leak(): void
    {
        Storage::fake('public');

        $userA = User::factory()->create(['name' => 'Pro A', 'role' => UserRole::PROFESSIONAL]);
        $userB = User::factory()->create(['name' => 'Pro B', 'role' => UserRole::PROFESSIONAL]);

        $photoA = UploadedFile::fake()->image('photoA.jpg')->store('profiles', 'public');
        $photoB = UploadedFile::fake()->image('photoB.jpg')->store('profiles', 'public');

        $profileA = ProfessionalProfile::factory()->create(['user_id' => $userA->id, 'profile_photo' => $photoA]);
        $profileB = ProfessionalProfile::factory()->create(['user_id' => $userB->id, 'profile_photo' => $photoB]);

        $urlA = Storage::disk('public')->url($photoA);
        $urlB = Storage::disk('public')->url($photoB);

        $this->assertNotEquals($urlA, $urlB);
        $this->assertEquals($urlA, $profileA->profilePhotoUrl());
        $this->assertEquals($urlB, $profileB->profilePhotoUrl());
    }
}
