<?php

namespace Tests\Feature;

use App\Enums\PriceType;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_searches_by_service_category_and_professional(): void
    {
        $category = Category::factory()->create(['name' => 'Plomería', 'slug' => 'plomeria']);
        $professional = ProfessionalProfile::factory()->create([
            'city' => 'Puebla',
            'bio' => 'Especialista en instalaciones de agua.',
        ]);
        $professional->user->update(['name' => 'Carlos Plomero']);
        $service = Service::factory()->create([
            'professional_id' => $professional->id,
            'category_id' => $category->id,
            'title' => 'Reparación de fugas',
            'price' => 800,
        ]);

        $this->get(route('marketplace.search', ['q' => 'fugas']))
            ->assertOk()
            ->assertSee($service->title)
            ->assertSee($professional->user->name);
        $this->get(route('marketplace.search', ['category' => 'plomeria']))
            ->assertOk()
            ->assertSee($service->title);
        $this->get(route('marketplace.search', ['q' => 'Carlos Plomero']))
            ->assertOk()
            ->assertSee($service->title);
    }

    public function test_marketplace_applies_price_city_rating_and_sort_filters(): void
    {
        $category = Category::factory()->create();
        $puebla = ProfessionalProfile::factory()->create(['city' => 'Puebla', 'average_rating' => 4.8, 'total_reviews' => 8]);
        $jalisco = ProfessionalProfile::factory()->create(['city' => 'Guadalajara', 'average_rating' => 3.5, 'total_reviews' => 4]);
        $low = Service::factory()->create(['professional_id' => $puebla->id, 'category_id' => $category->id, 'price' => 300, 'price_type' => PriceType::FIXED]);
        Service::factory()->create(['professional_id' => $jalisco->id, 'category_id' => $category->id, 'price' => 1200, 'price_type' => PriceType::STARTING_AT]);

        $this->get(route('marketplace.search', ['city' => 'Puebla', 'min_price' => 200, 'max_price' => 500, 'rating' => 4, 'price_type' => 'fixed']))
            ->assertOk()
            ->assertSee($low->title);
        $this->get(route('marketplace.search', ['sort' => 'price_low']))
            ->assertOk()
            ->assertViewHas('services', fn ($services) => $services->first()->id === $low->id);
    }

    public function test_marketplace_validates_filters_and_keeps_pagination(): void
    {
        $category = Category::factory()->create();
        $professional = ProfessionalProfile::factory()->create();
        Service::factory()->count(13)->create(['professional_id' => $professional->id, 'category_id' => $category->id]);

        $this->get(route('marketplace.search', ['q' => str_repeat('x', 101)]))
            ->assertRedirect()
            ->assertSessionHasErrors('q');
        $this->get(route('marketplace.search', ['category' => 'not-active']))
            ->assertRedirect()
            ->assertSessionHasErrors('category');
        $this->get(route('marketplace.search', ['category' => $category->slug, 'page' => 2]))
            ->assertOk()
            ->assertViewHas('services', fn ($services) => $services->currentPage() === 2 && $services->perPage() === 12);
    }

    public function test_marketplace_hides_inactive_categories_services_and_users(): void
    {
        $activeCategory = Category::factory()->create();
        $inactiveCategory = Category::factory()->create(['is_active' => false]);
        $visible = ProfessionalProfile::factory()->create();
        $suspended = ProfessionalProfile::factory()->create();
        $suspended->user->update(['status' => UserStatus::SUSPENDED]);
        $visibleService = Service::factory()->create(['professional_id' => $visible->id, 'category_id' => $activeCategory->id, 'title' => 'Visible marketplace service']);
        Service::factory()->create(['professional_id' => $visible->id, 'category_id' => $inactiveCategory->id, 'title' => 'Hidden inactive category']);
        Service::factory()->create(['professional_id' => $suspended->id, 'category_id' => $activeCategory->id, 'title' => 'Hidden suspended professional']);
        $deleted = Service::factory()->create(['professional_id' => $visible->id, 'category_id' => $activeCategory->id, 'title' => 'Hidden deleted service']);
        $deleted->delete();

        $this->get(route('marketplace.search'))->assertOk()
            ->assertSee($visibleService->title)
            ->assertDontSee('Hidden inactive category')
            ->assertDontSee('Hidden suspended professional')
            ->assertDontSee('Hidden deleted service');
    }

    public function test_categories_and_public_profile_only_expose_verified_active_content_without_private_data(): void
    {
        $category = Category::factory()->create(['name' => 'Carpintería', 'slug' => 'carpinteria']);
        $user = User::factory()->professional()->create(['name' => 'Ana Pública', 'email' => 'ana-private@example.test', 'phone' => '5511112222']);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $user->id, 'bio' => 'Muebles a medida.']);
        $service = Service::factory()->create(['professional_id' => $profile->id, 'category_id' => $category->id, 'title' => 'Muebles a medida']);
        Service::factory()->create(['professional_id' => $profile->id, 'category_id' => $category->id, 'title' => 'Servicio desactivado', 'is_active' => false]);

        $this->get(route('marketplace.categories'))->assertOk()->assertSee($category->name);
        $this->get(route('marketplace.category', $category))->assertOk()->assertSee($service->title);
        $this->get(route('professional.public-profile', $profile))->assertOk()
            ->assertSee('Ana Pública')
            ->assertSee('Muebles a medida')
            ->assertDontSee($user->email)
            ->assertDontSee($user->phone)
            ->assertDontSee('Servicio desactivado');

        $profile->update(['verification_status' => VerificationStatus::PENDING]);
        $this->get(route('professional.public-profile', $profile))->assertNotFound();
    }

    public function test_public_service_detail_requires_public_visibility_and_loads_images(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        $profile = ProfessionalProfile::factory()->create();
        $service = Service::factory()->create(['professional_id' => $profile->id, 'category_id' => $category->id, 'description' => 'Servicio público de prueba.']);
        $service->images()->create(['path' => 'services/test.jpg', 'alt_text' => 'Imagen del servicio', 'is_cover' => true]);

        $this->get(route('marketplace.service', $service))->assertOk()
            ->assertSee($service->title)
            ->assertSee('Imagen del servicio')
            ->assertDontSee($profile->user->email)
            ->assertDontSee($profile->user->phone);

        $category->update(['is_active' => false]);
        $this->get(route('marketplace.service', $service))->assertNotFound();
    }

    public function test_clients_can_toggle_unique_favorites_and_view_their_list(): void
    {
        $client = User::factory()->client()->create();
        $profile = ProfessionalProfile::factory()->create();

        $this->actingAs($client)->post(route('professional.favorite.toggle', $profile))
            ->assertRedirect()
            ->assertSessionHas('status');
        $this->assertDatabaseHas('favorites', ['user_id' => $client->id, 'professional_id' => $profile->id]);
        $this->assertSame(1, Favorite::count());
        $this->actingAs($client)->post(route('professional.favorite.toggle', $profile));
        $this->assertDatabaseMissing('favorites', ['user_id' => $client->id, 'professional_id' => $profile->id]);
        $this->actingAs($client)->get(route('client.favorites.index'))->assertOk()->assertSee('Todavía no tienes favoritos');

        $this->actingAs($client)->post(route('professional.favorite.toggle', $profile));
        $this->actingAs($client)->get(route('client.favorites.index'))->assertOk()->assertSee($profile->user->name);
    }

    public function test_guests_and_non_clients_cannot_write_favorites(): void
    {
        $profile = ProfessionalProfile::factory()->create();
        $this->post(route('professional.favorite.toggle', $profile))->assertRedirect(route('login'));
        $professional = User::factory()->professional()->create();
        $this->actingAs($professional)->post(route('professional.favorite.toggle', $profile))->assertForbidden();
    }
}
