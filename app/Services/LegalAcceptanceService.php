<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LegalAcceptanceService
{
    public function isRequired(): bool
    {
        return (bool) config('chambapp.legal.registration_acceptance_required', false);
    }

    public function isReady(): bool
    {
        if (! (bool) config('chambapp.legal.documents_final', false)) {
            return false;
        }

        return collect($this->configuredDocuments())
            ->every(fn (array $document): bool => filled($document['version'] ?? null)
                && ! str_starts_with(strtolower((string) $document['version']), 'draft'));
    }

    /** @return array<string, array{title: string, version: string, route: string}> */
    public function requiredDocuments(UserRole $role): array
    {
        $documents = $this->configuredDocuments();
        if ($role !== UserRole::PROFESSIONAL || ! (bool) config('chambapp.legal.professional_terms_enabled', false)) {
            unset($documents['professional_terms']);
        }

        return $documents;
    }

    /** @return list<array{document: string, title: string, version: string, url: string}> */
    public function publicDocuments(UserRole $role): array
    {
        return collect($this->requiredDocuments($role))->map(
            fn (array $document, string $type): array => [
                'document' => $type,
                'title' => $document['title'],
                'version' => $document['version'],
                'url' => route($document['route']),
            ],
        )->values()->all();
    }

    /** @return array<string, string> */
    public function validateRegistration(array $data, UserRole $role): array
    {
        if (! $this->isRequired()) {
            return [];
        }
        if (! $this->isReady()) {
            throw ValidationException::withMessages([
                'legal_accepted' => 'El registro está temporalmente detenido hasta publicar los documentos legales definitivos.',
            ]);
        }

        $validator = Validator::make($data, [
            'legal_accepted' => ['required', 'accepted'],
            'legal_documents' => ['required', 'array'],
            'legal_documents.*' => ['required', 'string', 'max:100'],
        ], [
            'legal_accepted.accepted' => 'Debes leer y aceptar los Términos y el Aviso de Privacidad.',
        ]);
        $validator->validate();

        $expected = collect($this->requiredDocuments($role))->mapWithKeys(
            fn (array $document, string $type): array => [$type => $document['version']],
        )->all();
        $submitted = $data['legal_documents'] ?? [];
        ksort($expected);
        if (is_array($submitted)) {
            ksort($submitted);
        }
        if (! is_array($submitted) || $submitted !== $expected) {
            throw ValidationException::withMessages([
                'legal_documents' => 'Los documentos o versiones legales no corresponden a los vigentes.',
            ]);
        }

        return $expected;
    }

    /** @param array<string, string> $documents */
    public function record(
        User $user,
        array $documents,
        string $platform,
        ?string $ipAddress,
        ?string $userAgent,
    ): void {
        foreach ($documents as $type => $version) {
            $user->legalAcceptances()->create([
                'document_type' => $type,
                'document_version' => $version,
                'accepted_at' => now(),
                'platform' => mb_substr($platform, 0, 30),
                'ip_hash' => filled($ipAddress) ? hash('sha256', (string) $ipAddress) : null,
                'user_agent_hash' => filled($userAgent) ? hash('sha256', (string) $userAgent) : null,
            ]);
        }
    }

    /** @return array<string, array{title: string, version: string, route: string}> */
    private function configuredDocuments(): array
    {
        return array_filter(
            (array) config('chambapp.legal.documents', []),
            fn (mixed $document): bool => is_array($document) && ($document['enabled'] ?? true),
        );
    }
}
