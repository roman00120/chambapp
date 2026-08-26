<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDiditWebhook;
use App\Models\DiditWebhookEvent;
use App\Services\DiditWebhookSignature;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiditWebhookController extends Controller
{
    public function __invoke(Request $request, DiditWebhookSignature $signature): JsonResponse
    {
        if (! $signature->isValid($request, config('services.didit.webhook_secret'))) {
            Log::warning('Didit webhook signature rejected', ['ip' => $request->ip()]);

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = $request->json()->all();
        $eventId = $payload['event_id'] ?? null;
        $sessionId = $payload['session_id'] ?? null;
        $webhookType = $payload['webhook_type'] ?? null;
        if (! is_string($eventId) || blank($eventId)
            || ! is_string($sessionId) || blank($sessionId)
            || ! is_string($webhookType) || blank($webhookType)) {
            return response()->json(['message' => 'Invalid payload'], 422);
        }

        $payloadHash = hash('sha256', $request->getContent());

        try {
            $event = DiditWebhookEvent::query()->create([
                'event_id' => mb_substr($eventId, 0, 191),
                'webhook_type' => mb_substr($webhookType, 0, 100),
                'provider_session_id' => mb_substr($sessionId, 0, 191),
                'payload_hash' => $payloadHash,
                'processing_status' => 'received',
                'received_at' => now(),
            ]);
        } catch (QueryException $exception) {
            $existing = DiditWebhookEvent::query()->where('event_id', mb_substr($eventId, 0, 191))->first();
            if ($existing && hash_equals($existing->payload_hash, $payloadHash)) {
                return response()->json(['ok' => true, 'duplicate' => true]);
            }

            Log::warning('Didit webhook event id collision', ['event_id_hash' => hash('sha256', $eventId)]);

            return response()->json(['message' => 'Event conflict'], 409);
        }

        ProcessDiditWebhook::dispatch($event->id);

        return response()->json(['ok' => true], 202);
    }
}
