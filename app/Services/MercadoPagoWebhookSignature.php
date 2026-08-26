<?php

namespace App\Services;

use Illuminate\Http\Request;

class MercadoPagoWebhookSignature
{
    public function isValid(Request $request, ?string $secret): bool
    {
        if (! filled($secret)) {
            return false;
        }

        $parts = collect(explode(',', (string) $request->header('x-signature')))
            ->mapWithKeys(function (string $part): array {
                [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);

                return $key && $value ? [$key => $value] : [];
            });
        $timestamp = $parts->get('ts');
        $provided = $parts->get('v1');
        $dataId = (string) (
            $request->query('data.id')
            ?: $request->query('data_id')
            ?: data_get($request->input('data'), 'id', '')
            ?: (preg_match('/[?&]data\.id=([^&]+)/', (string) $request->server('QUERY_STRING', ''), $m) ? urldecode($m[1]) : '')
        );

        if (! filled($timestamp) || ! ctype_digit((string) $timestamp) || ! filled($provided)) {
            return false;
        }
        $timestampSeconds = (int) $timestamp;
        if ($timestampSeconds > 9999999999) {
            $timestampSeconds = intdiv($timestampSeconds, 1000);
        }
        if (abs(now()->timestamp - $timestampSeconds) > 300) {
            return false;
        }

        $requestId = (string) $request->header('x-request-id', '');
        $manifest = '';
        if ($dataId !== '') {
            $manifest .= 'id:'.$dataId.';';
        }
        if ($requestId !== '') {
            $manifest .= 'request-id:'.$requestId.';';
        }
        $manifest .= 'ts:'.$timestamp.';';
        $calculated = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($calculated, $provided);
    }
}
