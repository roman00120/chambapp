<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class AdminDateRangeService
{
    public function resolve(Request $request): array
    {
        $range = $request->string('range', '30')->toString();
        $now = CarbonImmutable::now();

        if ($range === 'today') {
            return [$now->startOfDay(), $now->endOfDay(), $range];
        }
        if ($range === '7') {
            return [$now->subDays(6)->startOfDay(), $now->endOfDay(), $range];
        }
        if ($range === 'month') {
            return [$now->startOfMonth(), $now->endOfDay(), $range];
        }
        if ($range === 'custom') {
            $start = $this->date($request->input('from'), $now->subDays(29)->startOfDay());
            $end = $this->date($request->input('to'), $now->endOfDay())->endOfDay();
            if ($start->gt($end)) {
                [$start, $end] = [$end->startOfDay(), $start->endOfDay()];
            }

            return [$start, $end, $range];
        }

        return [$now->subDays(29)->startOfDay(), $now->endOfDay(), '30'];
    }

    private function date(mixed $value, CarbonImmutable $fallback): CarbonImmutable
    {
        try {
            return CarbonImmutable::createFromFormat('Y-m-d', (string) $value)->startOfDay();
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
