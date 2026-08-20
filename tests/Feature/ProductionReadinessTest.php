<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_reports_application_and_database_are_available(): void
    {
        $this->getJson(route('health'))
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }

    public function test_security_headers_are_present_on_web_responses(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_legal_pages_and_public_sitemap_are_available(): void
    {
        $this->get(route('legal.terms'))->assertOk()->assertSee('Términos y condiciones');
        $this->get(route('legal.privacy'))->assertOk()->assertSee('Aviso de privacidad');
        $this->get(route('sitemap'))->assertOk()->assertHeader('Content-Type', 'application/xml')->assertSee('<urlset', false);
    }

    public function test_pwa_deliverables_do_not_contain_private_configuration(): void
    {
        $manifestPath = public_path('manifest.webmanifest');
        $serviceWorkerPath = public_path('sw.js');
        $offlinePath = public_path('offline.html');

        $this->assertFileExists($manifestPath);
        $this->assertFileExists($serviceWorkerPath);
        $this->assertFileExists($offlinePath);
        $this->assertFileExists(public_path('images/pwa/icon-192.png'));
        $this->assertFileExists(public_path('images/pwa/icon-512.png'));
        $this->assertStringNotContainsString('MERCADOPAGO_CLIENT_SECRET', file_get_contents($manifestPath));
        $this->assertStringNotContainsString('MERCADOPAGO_CLIENT_SECRET', file_get_contents($serviceWorkerPath));
    }

    public function test_production_example_requires_safe_debug_and_database_sessions(): void
    {
        $environment = file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString('APP_DEBUG=false', $environment);
        $this->assertStringContainsString('SESSION_DRIVER=database', $environment);
        $this->assertStringContainsString('CHAMBAPP_PLATFORM_FEE_PERCENT=15', $environment);
        $this->assertStringContainsString('MERCADOPAGO_WEBHOOK_SECRET=', $environment);
    }
}
