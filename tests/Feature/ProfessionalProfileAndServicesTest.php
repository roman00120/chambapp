<?php

namespace Tests\Feature;

use App\Enums\PriceType;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Models\ServiceImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfessionalProfileAndServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_professional_can_view_and_update_its_profile(): void
    {
        [$user, $profile] = $this->professional();

        $this->actingAs($user)->get(route('professional.profile.show'))
            ->assertOk()
            ->assertSee('Mi perfil profesional');

        $this->actingAs($user)->put(route('professional.profile.update'), [
            'name' => 'Profesional Actualizado',
            'phone' => '5512345678',
            'bio' => 'Especialista en mantenimiento y reparaciones para el hogar.',
            'experience_years' => 5,
            'city' => 'Puebla',
            'state' => 'Puebla',
            'postal_code' => '72000',
        ])->assertRedirect(route('professional.profile.show'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Profesional Actualizado', 'phone' => '5512345678']);
        $this->assertDatabaseHas('professional_profiles', [
            'id' => $profile->id,
            'bio' => 'Especialista en mantenimiento y reparaciones para el hogar.',
            'experience_years' => 5,
            'city' => 'Puebla',
        ]);
    }

    public function test_clients_cannot_access_or_update_professional_profile(): void
    {
        $client = User::factory()->client()->create();

        $this->actingAs($client)->get(route('professional.profile.show'))->assertForbidden();
        $this->actingAs($client)->put(route('professional.profile.update'), [])->assertForbidden();
    }

    public function test_profile_system_fields_cannot_be_changed_from_the_form(): void
    {
        [$user, $profile] = $this->professional();
        $profile->update([
            'verification_status' => VerificationStatus::VERIFIED,
            'average_rating' => '4.50',
            'total_reviews' => 8,
            'total_completed_jobs' => 12,
        ]);

        $this->actingAs($user)->put(route('professional.profile.update'), [
            'name' => $user->name,
            'phone' => '5512345678',
            'experience_years' => 2,
            'verification_status' => 'rejected',
            'average_rating' => 1,
            'total_reviews' => 0,
            'total_completed_jobs' => 0,
        ])->assertRedirect(route('professional.profile.show'));

        $this->assertDatabaseHas('professional_profiles', [
            'id' => $profile->id,
            'verification_status' => VerificationStatus::VERIFIED->value,
            'average_rating' => '4.50',
            'total_reviews' => 8,
            'total_completed_jobs' => 12,
        ]);
    }

    public function test_profile_photo_uses_public_storage_and_replaces_managed_photo(): void
    {
        Storage::fake('public');
        [$user, $profile] = $this->professional();
        Storage::disk('public')->put('profiles/old-photo.jpg', 'old');
        $profile->update(['profile_photo' => 'profiles/old-photo.jpg']);

        $this->actingAs($user)->put(route('professional.profile.update'), [
            'name' => $user->name,
            'phone' => '5512345678',
            'experience_years' => 3,
            'profile_photo' => UploadedFile::fake()->create('new-photo.jpg', 100, 'image/jpeg'),
        ])->assertRedirect();

        $newPath = $profile->fresh()->profile_photo;
        Storage::disk('public')->assertMissing('profiles/old-photo.jpg');
        Storage::disk('public')->assertExists($newPath);
        $this->assertStringStartsWith('profiles/', $newPath);
    }

    public function test_invalid_profile_photo_is_rejected(): void
    {
        [$user] = $this->professional();

        $this->actingAs($user)->put(route('professional.profile.update'), [
            'name' => $user->name,
            'phone' => '5512345678',
            'experience_years' => 1,
            'profile_photo' => UploadedFile::fake()->create('malicious.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('profile_photo');
    }

    public function test_a_professional_can_create_a_service_with_a_cover_image(): void
    {
        Storage::fake('public');
        [$user, $profile] = $this->professional();
        $category = Category::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->post(route('professional.services.store'), [
            'category_id' => $category->id,
            'title' => 'Instalación de boiler residencial',
            'description' => 'Instalación segura y mantenimiento preventivo para tu boiler residencial.',
            'price_type' => PriceType::FIXED->value,
            'price' => '1250.00',
            'professional_id' => 99999,
            'is_featured' => true,
            'images' => [UploadedFile::fake()->create('boiler.jpg', 100, 'image/jpeg')],
        ]);

        $service = Service::query()->where('professional_id', $profile->id)->firstOrFail();
        $response->assertRedirect(route('professional.services.edit', $service));
        $this->actingAs($user)->get(route('professional.services.index'))->assertOk()->assertSee($service->title);
        $this->actingAs($user)->get(route('professional.services.create'))->assertOk()->assertSee('Publica lo que sabes hacer.');
        $this->actingAs($user)->get(route('professional.services.edit', $service))->assertOk()->assertSee('Actualiza tu publicación.');
        $this->assertSame($profile->id, $service->professional_id);
        $this->assertFalse($service->is_featured);
        $this->assertNotSame('', $service->slug);
        $this->assertDatabaseHas('service_images', ['service_id' => $service->id, 'is_cover' => true]);
        Storage::disk('public')->assertExists($service->images()->first()->path);
    }

    public function test_service_price_rules_and_active_category_are_enforced(): void
    {
        [$user, $profile] = $this->professional();
        $category = Category::factory()->create(['is_active' => true]);
        $base = [
            'category_id' => $category->id,
            'title' => 'Servicio de mantenimiento general',
            'description' => 'Mantenimiento general para conservar tu espacio en buenas condiciones.',
        ];

        $debugResponse = $this->actingAs($user)->from(route('professional.services.create'))->post(route('professional.services.store'), $base + [
            'price_type' => PriceType::FIXED->value,
        ]);
        $debugResponse->assertRedirect(route('professional.services.create'));
        $this->assertTrue(session()->has('errors'));

        $this->actingAs($user)->from(route('professional.services.create'))->post(route('professional.services.store'), $base + [
            'price_type' => PriceType::QUOTE->value,
        ])->assertRedirect();

        $quote = $profile->services()->latest('id')->firstOrFail();
        $this->assertSame(PriceType::QUOTE, $quote->price_type);
        $this->assertNull($quote->price);

        $inactive = Category::factory()->create(['is_active' => false]);
        $this->actingAs($user)->from(route('professional.services.create'))->post(route('professional.services.store'), array_merge($base, [
            'category_id' => $inactive->id,
            'price_type' => PriceType::STARTING_AT->value,
            'price' => 500,
        ]))->assertRedirect(route('professional.services.create'));
        $this->assertTrue(session()->has('errors'));
    }

    public function test_clients_cannot_create_services(): void
    {
        $client = User::factory()->client()->create();

        $this->actingAs($client)->get(route('professional.services.index'))->assertForbidden();
        $this->actingAs($client)->post(route('professional.services.store'), [])->assertForbidden();
    }

    public function test_professional_can_update_toggle_and_delete_only_its_own_service(): void
    {
        [$owner, $profile] = $this->professional();
        [$other, $otherProfile] = $this->professional();
        $category = Category::factory()->create(['is_active' => true]);
        $service = Service::factory()->create(['professional_id' => $profile->id, 'category_id' => $category->id]);
        $otherService = Service::factory()->create(['professional_id' => $otherProfile->id, 'category_id' => $category->id]);
        $otherImage = ServiceImage::create(['service_id' => $otherService->id, 'path' => 'services/other/image.jpg', 'is_cover' => true]);
        $payload = [
            'category_id' => $category->id,
            'title' => 'Servicio actualizado correctamente',
            'description' => 'Descripción actualizada con suficiente detalle para validar el formulario.',
            'price_type' => PriceType::STARTING_AT->value,
            'price' => 900,
        ];

        $this->actingAs($other)->get(route('professional.services.edit', $service))->assertForbidden();
        $this->actingAs($owner)->delete(route('professional.service-images.destroy', $otherImage))->assertForbidden();
        $this->actingAs($owner)->put(route('professional.services.update', $service), $payload)->assertRedirect();
        $this->assertDatabaseHas('services', ['id' => $service->id, 'title' => 'Servicio actualizado correctamente']);

        $this->actingAs($owner)->patch(route('professional.services.toggle', $service))->assertRedirect();
        $this->assertDatabaseHas('services', ['id' => $service->id, 'is_active' => false]);

        $this->actingAs($other)->delete(route('professional.services.destroy', $service))->assertForbidden();
        $this->actingAs($owner)->delete(route('professional.services.destroy', $service))->assertRedirect(route('professional.services.index'));
        $this->assertSoftDeleted('services', ['id' => $service->id]);
        $this->assertDatabaseHas('services', ['id' => $otherService->id]);
    }

    public function test_service_images_are_limited_and_cover_is_reassigned_after_deletion(): void
    {
        Storage::fake('public');
        [$user, $profile] = $this->professional();
        $category = Category::factory()->create(['is_active' => true]);
        $service = Service::factory()->create(['professional_id' => $profile->id, 'category_id' => $category->id]);

        foreach (range(0, 4) as $index) {
            $path = "services/{$service->id}/image-{$index}.jpg";
            Storage::disk('public')->put($path, 'image');
            ServiceImage::create([
                'service_id' => $service->id,
                'path' => $path,
                'sort_order' => $index,
                'is_cover' => $index === 0,
            ]);
        }

        $this->actingAs($user)->put(route('professional.services.update', $service), [
            'category_id' => $category->id,
            'title' => $service->title,
            'description' => 'Descripción actualizada con suficiente detalle para validar el formulario.',
            'price_type' => PriceType::QUOTE->value,
            'images' => [UploadedFile::fake()->create('sixth.jpg', 100, 'image/jpeg')],
        ])->assertSessionHasErrors('images');

        $replacementCover = $service->images()->orderBy('sort_order')->skip(2)->firstOrFail();
        $this->actingAs($user)->put(route('professional.services.update', $service), [
            'category_id' => $category->id,
            'title' => $service->title,
            'description' => 'Descripción actualizada con suficiente detalle para validar el formulario.',
            'price_type' => PriceType::QUOTE->value,
            'cover_image_id' => $replacementCover->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('service_images', ['id' => $replacementCover->id, 'is_cover' => true]);

        $cover = $replacementCover->fresh();
        $this->actingAs($user)->delete(route('professional.service-images.destroy', $cover))->assertRedirect();
        $this->assertDatabaseHas('service_images', ['service_id' => $service->id, 'is_cover' => true]);
        $this->assertSame(1, $service->images()->where('is_cover', true)->count());
    }

    public function test_public_profile_preparation_shows_only_verified_professionals_active_services(): void
    {
        [$user, $profile] = $this->professional();
        $profile->update(['verification_status' => VerificationStatus::VERIFIED]);
        $category = Category::factory()->create(['is_active' => true]);
        $visible = Service::factory()->create([
            'professional_id' => $profile->id,
            'category_id' => $category->id,
            'title' => 'Servicio visible en perfil público',
            'is_active' => true,
        ]);
        $hidden = Service::factory()->create([
            'professional_id' => $profile->id,
            'category_id' => $category->id,
            'title' => 'Servicio inactivo oculto',
            'is_active' => false,
        ]);

        $this->get(route('professional.public-profile', $profile))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($visible->title)
            ->assertDontSee($hidden->title);

        $profile->update(['verification_status' => VerificationStatus::PENDING]);
        $this->get(route('professional.public-profile', $profile))->assertNotFound();
    }

    private function professional(): array
    {
        $user = User::factory()->professional()->create();
        $profile = ProfessionalProfile::factory()->create([
            'user_id' => $user->id,
            'verification_status' => VerificationStatus::UNVERIFIED,
        ]);

        $this->assertSame(UserRole::PROFESSIONAL, $user->role);

        return [$user, $profile];
    }
}
