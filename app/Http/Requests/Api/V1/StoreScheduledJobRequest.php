<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\StoreScheduledJobRequestRequest;

class StoreScheduledJobRequest extends StoreScheduledJobRequestRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('title')) {
            $this->merge(['title' => 'Solicitud programada']);
        }
    }
}
