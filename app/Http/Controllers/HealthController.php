<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $exception) {
            Log::error('Health check database connection failed', ['exception' => $exception::class]);

            return response()->json(['status' => 'unhealthy'], 503);
        }

        return response()->json(['status' => 'ok']);
    }
}
