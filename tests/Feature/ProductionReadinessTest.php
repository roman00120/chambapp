<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
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
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');
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
        $this->assertStringContainsString('CACHE_STORE=database', $environment);
        $this->assertStringContainsString('CHAMBAPP_PLATFORM_FEE_PERCENT=15', $environment);
        $this->assertStringContainsString('MERCADOPAGO_WEBHOOK_SECRET=', $environment);
        $this->assertStringContainsString('MERCADOPAGO_USER_ID=', $environment);
    }

    public function test_production_preflight_rejects_unsafe_configuration_without_exposing_secrets(): void
    {
        $sensitiveValue = 'never-print-this-secret';

        config([
            'app.env' => 'local',
            'app.debug' => true,
            'app.url' => 'http://localhost:8000',
            'database.default' => 'sqlite',
            'services.mercadopago.client_secret' => $sensitiveValue,
        ]);

        $exitCode = Artisan::call('production:preflight');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Despliegue bloqueado', $output);
        $this->assertStringNotContainsString($sensitiveValue, $output);
    }

    public function test_production_preflight_accepts_a_complete_safe_configuration(): void
    {
        $originalDatabase = config('database.default');

        config([
            'app.env' => 'production',
            'app.debug' => false,
            'app.url' => 'https://chambapp.mx',
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'chambapp',
            'database.connections.mysql.username' => 'chambapp_app',
            'database.connections.mysql.password' => 'database-secret',
            'session.driver' => 'database',
            'session.secure' => true,
            'session.http_only' => true,
            'queue.default' => 'database',
            'cache.default' => 'database',
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.chambapp.mx',
            'mail.mailers.smtp.username' => 'mailer',
            'mail.mailers.smtp.password' => 'mail-secret',
            'mail.from.address' => 'notificaciones@chambapp.mx',
            'services.mercadopago.client_id' => 'production-client-id',
            'services.mercadopago.client_secret' => 'production-client-secret',
            'services.mercadopago.webhook_secret' => 'production-webhook-secret',
            'services.mercadopago.access_token' => 'production-access-token',
            'services.mercadopago.user_id' => 'production-user-id',
            'services.mercadopago.api_url' => 'https://api.mercadopago.com',
            'services.mercadopago.auth_url' => 'https://auth.mercadopago.com.mx/authorization',
            'chambapp.payments.currency' => 'MXN',
            'chambapp.payments.platform_fee_percent' => '15',
            'chambapp.payments.checkout_timeout' => 10,
            'chambapp.payments.preference_lifetime_hours' => 24,
            'cors.allowed_origins' => ['https://chambapp.mx'],
        ]);

        $exitCode = Artisan::call('production:preflight');
        $output = Artisan::output();
        config(['database.default' => $originalDatabase]);

        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Preflight aprobado', $output);
    }
}
