<?php

namespace Tests\Feature;

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class CategoryIconResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_electricidad_and_limpieza_have_valid_bootstrap_icons(): void
    {
        $this->seed(CategorySeeder::class);

        $electricidad = Category::where('slug', 'electricidad')->firstOrFail();
        $this->assertEquals('lightning-charge', $electricidad->bootstrapIcon());

        $limpieza = Category::where('slug', 'limpieza')->firstOrFail();
        $this->assertEquals('stars', $limpieza->bootstrapIcon());
    }

    public function test_all_seeded_categories_have_valid_bootstrap_icons(): void
    {
        $this->seed(CategorySeeder::class);

        $categories = Category::active()->get();
        $this->assertNotEmpty($categories);

        foreach ($categories as $category) {
            $icon = $category->bootstrapIcon();
            $this->assertNotEmpty($icon, "Category {$category->name} has empty icon");
            $this->assertNotEquals('bolt', $icon, "Category {$category->name} still has invalid bolt icon");
            $this->assertNotEquals('sparkles', $icon, "Category {$category->name} still has invalid sparkles icon");
        }
    }

    public function test_unknown_icon_or_null_uses_fallback_grid(): void
    {
        $category = Category::factory()->create([
            'name' => 'Categoría Desconocida',
            'slug' => 'categoria-desconocida',
            'icon' => null,
        ]);

        $this->assertEquals('grid', $category->bootstrapIcon());
    }

    public function test_home_and_categories_views_render_valid_icon_classes(): void
    {
        $this->seed(CategorySeeder::class);

        $electricidad = Category::where('slug', 'electricidad')->firstOrFail();
        $limpieza = Category::where('slug', 'limpieza')->firstOrFail();

        $homeRender = Blade::render(
            '<a class="reference-category" href="#"><span><i class="bi bi-{{ $category->bootstrap_icon ?: ($category->icon ?: \'grid\') }}"></i></span><strong>{{ $category->name }}</strong></a>',
            ['category' => $electricidad]
        );
        $this->assertStringContainsString('bi-lightning-charge', $homeRender);

        $limpiezaRender = Blade::render(
            '<a class="reference-category" href="#"><span><i class="bi bi-{{ $category->bootstrap_icon ?: ($category->icon ?: \'grid\') }}"></i></span><strong>{{ $category->name }}</strong></a>',
            ['category' => $limpieza]
        );
        $this->assertStringContainsString('bi-stars', $limpiezaRender);
    }

    public function test_category_api_resource_returns_normalized_icon(): void
    {
        $this->seed(CategorySeeder::class);

        $response = $this->getJson('/api/v1/categories');
        $response->assertOk();
        $response->assertJsonFragment(['slug' => 'electricidad', 'icon' => 'lightning-charge']);
        $response->assertJsonFragment(['slug' => 'limpieza', 'icon' => 'stars']);
    }
}
