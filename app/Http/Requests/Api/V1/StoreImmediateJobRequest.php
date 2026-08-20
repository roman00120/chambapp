<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\StoreImmediateJobRequestRequest;

class StoreImmediateJobRequest extends StoreImmediateJobRequestRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('title')) {
            $this->merge(['title' => 'Solicitud inmediata']);
        }
    }
}
