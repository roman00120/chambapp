<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisualSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_contains_the_visual_discovery_sections(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertViewIs('welcome')
            ->assertSee('¿Qué servicio')
            ->assertSee('categorias')
            ->assertSee('profesionales')
            ->assertSee('servicios')
            ->assertSee('Crear cuenta');
    }

    public function test_authentication_pages_use_the_public_shell(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('public-shell', false);
        $this->get(route('register'))->assertOk()->assertSee('public-shell', false);
    }

    public function test_each_dashboard_exposes_role_specific_mobile_navigation(): void
    {
        $client = User::factory()->client()->create();
        $professional = User::factory()->professional()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($client)->get(route('client.dashboard'))
            ->assertOk()->assertSee('Buscar')->assertSee('Trabajos');
        $this->actingAs($professional)->get(route('professional.dashboard'))
            ->assertOk()->assertSee('Servicios')->assertSee('Solicitudes');
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()->assertSee('Usuarios')->assertSee('Reportes');
    }

    public function test_custom_not_found_page_is_rendered(): void
    {
        $this->get('/esta-ruta-no-existe')
            ->assertNotFound()
            ->assertSee('No encontramos esta página.');
    }
}
