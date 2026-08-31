<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_all_legal_pages_load_successfully(): void
    {
        $pages = [
            route('legal.terms') => 'Términos y Condiciones',
            route('legal.privacy') => 'Aviso de Privacidad',
            route('legal.cookies') => 'Política de Cookies',
            route('legal.cancellations') => 'Cancelaciones y Reembolsos',
            route('legal.professionals') => 'Para Profesionales',
            route('legal.contact') => 'Contacto y Soporte',
        ];

        foreach ($pages as $url => $expectedTitle) {
            $response = $this->get($url);
            $response->assertOk();
            $response->assertSee($expectedTitle, false);
            $response->assertDontSee('Borrador sujeto a revisión jurídica');
            $response->assertDontSee('Lorem ipsum');
            $response->assertDontSee('TODO');
        }
    }

    public function test_footer_contains_all_legal_links(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee(route('legal.terms'));
        $response->assertSee(route('legal.privacy'));
        $response->assertSee(route('legal.cookies'));
        $response->assertSee(route('legal.cancellations'));
        $response->assertSee(route('legal.professionals'));
        $response->assertSee(route('legal.contact'));
    }
}
