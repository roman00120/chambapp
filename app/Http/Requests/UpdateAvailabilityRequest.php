<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isProfessional() === true;
    }

    public function rules(): array
    {
        return [
            'is_available' => ['required', 'boolean'],
            'service_radius_km' => ['required', 'integer', Rule::in(config('chambapp.on_demand.service_radius_options_km', [5, 10, 15, 25]))],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
