<?php

namespace App\Services;

class ContactInformationGuard
{
    public const MESSAGE = 'Para proteger tu contratación, no compartas teléfono, correo, redes sociales o enlaces antes de realizar el pago en Chambapp.';

    public function containsRestrictedInformation(?string $text): bool
    {
        if (! filled($text)) {
            return false;
        }

        $text = trim($text);

        return preg_match('/[\p{L}\d._%+\-]+\s*@\s*[\p{L}\d.\-]+\s*\.\s*[\p{L}]{2,}/iu', $text) === 1
            || preg_match('/(?:https?:\/\/|www\.|wa\.me|t\.me|[\w.-]+\.(?:com|mx|net|org)\b)/iu', $text) === 1
            || preg_match('/(?<!\d)(?:\+?52[\s.-]?)?(?:\d[\s.-]?){10}(?!\d)/u', $text) === 1
            || preg_match('/\b(?:m[aá]ndame|m[aá]ndame|escr[ií]beme|b[uú]scame|h[aá]blame|cont[aá]ctame|s[ií]gueme)\b.{0,40}\b(?:whatsapp|telegram|instagram|facebook)\b/iu', $text) === 1
            || preg_match('/\b(?:whatsapp|telegram|instagram|facebook)\b.{0,40}\b(?:m[aá]ndame|escr[ií]beme|b[uú]scame|h[aá]blame|cont[aá]ctame|s[ií]gueme)\b/iu', $text) === 1;
    }
}
