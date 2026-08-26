<?php

namespace App\Services;

use Illuminate\Http\Request;
use stdClass;

class DiditWebhookSignature
{
    public function isValid(Request $request, ?string $secret): bool
    {
        if (! filled($secret)) {
            return false;
        }

        $timestamp = $request->header('X-Timestamp');
        if (! is_string($timestamp) || ! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $rawBody = $request->getContent();
        $decodedBody = json_decode($rawBody, true);
        $bodyTimestamp = is_array($decodedBody) ? ($decodedBody['timestamp'] ?? null) : null;
        if (! is_int($bodyTimestamp) && ! (is_string($bodyTimestamp) && ctype_digit($bodyTimestamp))) {
            return false;
        }

        if (abs((int) $bodyTimestamp - (int) $timestamp) > 5) {
            return false;
        }

        $signatureV2 = $request->header('X-Signature-V2');
        if (is_string($signatureV2) && $this->verifyV2($rawBody, $signatureV2, $secret)) {
            return true;
        }

        $signatureRaw = $request->header('X-Signature');
        if (is_string($signatureRaw) && $this->matches($rawBody, $signatureRaw, $secret)) {
            return true;
        }

        $signatureSimple = $request->header('X-Signature-Simple');

        return is_string($signatureSimple)
            && $this->verifySimple($decodedBody, $signatureSimple, $secret);
    }

    private function verifyV2(string $rawBody, string $signature, string $secret): bool
    {
        $decoded = json_decode($rawBody, false);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        $canonical = json_encode(
            $this->canonicalize($decoded),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );

        return is_string($canonical) && $this->matches($canonical, $signature, $secret);
    }

    /** @param array<string, mixed> $body */
    private function verifySimple(array $body, string $signature, string $secret): bool
    {
        $canonical = implode(':', [
            $body['timestamp'] ?? '',
            $body['session_id'] ?? '',
            $body['status'] ?? '',
            $body['webhook_type'] ?? '',
        ]);

        return $this->matches($canonical, $signature, $secret);
    }

    private function matches(string $value, string $signature, string $secret): bool
    {
        $expected = hash_hmac('sha256', $value, $secret);

        return strlen($signature) === strlen($expected) && hash_equals($expected, strtolower($signature));
    }

    private function canonicalize(mixed $value): mixed
    {
        if ($value instanceof stdClass) {
            $properties = get_object_vars($value);
            ksort($properties, SORT_STRING);
            $sorted = new stdClass;
            foreach ($properties as $key => $item) {
                $sorted->{$key} = $this->canonicalize($item);
            }

            return $sorted;
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
        }

        if (is_float($value) && floor($value) === $value) {
            return (int) $value;
        }

        return $value;
    }
}
