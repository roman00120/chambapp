<?php

namespace Tests\Unit;

use App\Services\DiditWebhookSignature;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class DiditWebhookSignatureTest extends TestCase
{
    public function test_v2_signature_preserves_unicode_empty_objects_and_sorts_keys(): void
    {
        $timestamp = time();
        $raw = '{"status":"Approved","name":"José","risk":{},"items":[],"timestamp":'.$timestamp.'}';
        $canonical = '{"items":[],"name":"José","risk":{},"status":"Approved","timestamp":'.$timestamp.'}';
        $secret = 'didit-test-secret';
        $request = Request::create('/webhooks/didit', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_SIGNATURE_V2' => hash_hmac('sha256', $canonical, $secret),
        ], $raw);

        $this->assertTrue((new DiditWebhookSignature)->isValid($request, $secret));
    }

    public function test_raw_signature_is_accepted_and_stale_timestamp_is_rejected(): void
    {
        $timestamp = time();
        $raw = '{"event_id":"event-1","timestamp":'.$timestamp.'}';
        $secret = 'didit-test-secret';
        $signature = hash_hmac('sha256', $raw, $secret);

        $valid = Request::create('/webhooks/didit', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_SIGNATURE' => $signature,
        ], $raw);
        $stale = Request::create('/webhooks/didit', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TIMESTAMP' => (string) (time() - 301),
            'HTTP_X_SIGNATURE' => $signature,
        ], $raw);

        $service = new DiditWebhookSignature;
        $this->assertTrue($service->isValid($valid, $secret));
        $this->assertFalse($service->isValid($stale, $secret));
        $this->assertFalse($service->isValid($valid, 'wrong-secret'));
    }

    public function test_signed_body_timestamp_must_match_fresh_header_timestamp(): void
    {
        $bodyTimestamp = time() - 301;
        $raw = '{"event_id":"event-replay","timestamp":'.$bodyTimestamp.'}';
        $secret = 'didit-test-secret';
        $request = Request::create('/webhooks/didit', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_TIMESTAMP' => (string) time(),
            'HTTP_X_SIGNATURE' => hash_hmac('sha256', $raw, $secret),
        ], $raw);

        $this->assertFalse((new DiditWebhookSignature)->isValid($request, $secret));
    }
}
