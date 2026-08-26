<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\ProfessionalProfile;
use App\Models\User;
use App\Services\ProfessionalProfileService;
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

        // Test Edit Profile View preview
        $editView = $this->actingAs($user)->get(route('professional.profile.edit'));
        $editView->assertOk();
        $editView->assertSee($expectedUrl, false);
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

        $editView = $this->actingAs($user)->get(route('professional.profile.edit'));
        $editView->assertOk();
        $editView->assertSee('AR');
    }

    public function test_updating_profile_without_new_photo_preserves_existing_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'David Morales',
            'phone' => '3312345678',
            'role' => UserRole::PROFESSIONAL,
            'status' => UserStatus::ACTIVE,
        ]);

        $photo = UploadedFile::fake()->image('david.jpg', 300, 300);
        $path = $photo->store('profiles', 'public');

        $profile = ProfessionalProfile::factory()->create([
            'user_id' => $user->id,
            'profile_photo' => $path,
            'experience_years' => 5,
        ]);

        $service = app(ProfessionalProfileService::class);

        // Update basic info without sending photo
        $updated = $service->update($user, $profile, [
            'name' => 'David Morales Updated',
            'phone' => '3312345678',
            'bio' => 'Nueva biografía',
            'experience_years' => 6,
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            'postal_code' => '44100',
        ], null);

        $this->assertEquals($path, $updated->profile_photo);
        $this->assertTrue(Storage::disk('public')->exists($path));
    }

    public function test_updating_profile_with_new_photo_replaces_old_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'David Morales',
            'phone' => '3312345678',
            'role' => UserRole::PROFESSIONAL,
            'status' => UserStatus::ACTIVE,
        ]);

        $oldPhoto = UploadedFile::fake()->image('old.jpg', 300, 300);
        $oldPath = $oldPhoto->store('profiles', 'public');

        $profile = ProfessionalProfile::factory()->create([
            'user_id' => $user->id,
            'profile_photo' => $oldPath,
            'experience_years' => 5,
        ]);

        $newPhoto = UploadedFile::fake()->image('new.jpg', 400, 400);

        $service = app(ProfessionalProfileService::class);
        $updated = $service->update($user, $profile, [
            'name' => 'David Morales',
            'phone' => '3312345678',
            'bio' => 'Bio',
            'experience_years' => 5,
        ], $newPhoto);

        $this->assertNotEquals($oldPath, $updated->profile_photo);
        $this->assertFalse(Storage::disk('public')->exists($oldPath));
        $this->assertTrue(Storage::disk('public')->exists($updated->profile_photo));
    }
}
