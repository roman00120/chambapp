<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => data_get($this->data, 'type'),
            'title' => data_get($this->data, 'title'),
            'message' => data_get($this->data, 'body') ?? data_get($this->data, 'message'),
            'destination' => $this->safeDestination(),
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    private function safeDestination(): ?array
    {
        $url = data_get($this->data, 'url');
        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return null;
        }
        $configured = parse_url((string) config('app.url')) ?: [];
        if (isset($parts['host']) && strcasecmp((string) ($configured['host'] ?? ''), $parts['host']) !== 0) {
            return null;
        }
        if (isset($parts['scheme']) && ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return null;
        }

        $path = '/'.ltrim((string) ($parts['path'] ?? ''), '/');
        if (preg_match('#^/trabajos/(\d+)(?:/calificar)?/?$#', $path, $matches) === 1) {
            return ['kind' => 'job', 'id' => (int) $matches[1]];
        }
        if (preg_match('#^/profesionales/(\d+)/?$#', $path, $matches) === 1) {
            return ['kind' => 'professional', 'id' => (int) $matches[1]];
        }

        return null;
    }
}
