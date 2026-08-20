<?php

namespace Tests\Unit;

use App\Services\ContactInformationGuard;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContactInformationGuardTest extends TestCase
{
    #[DataProvider('restrictedTextProvider')]
    public function test_restricted_contact_information_is_detected(string $text): void
    {
        $this->assertTrue((new ContactInformationGuard)->containsRestrictedInformation($text));
    }

    public function test_normal_text_is_allowed(): void
    {
        $this->assertFalse((new ContactInformationGuard)->containsRestrictedInformation('Incluye mano de obra, materiales y garantía por escrito.'));
    }

    public static function restrictedTextProvider(): array
    {
        return [
            'phone with spaces' => ['Llámame al 55 12 34 56 78'],
            'phone with hyphens' => ['Mi teléfono es 55-12-34-56-78'],
            'phone without separators' => ['WhatsApp: 5512345678'],
            'spaced email' => ['correo @ gmail . com'],
            'url' => ['Consulta más en https://ejemplo.com'],
        ];
    }
}
