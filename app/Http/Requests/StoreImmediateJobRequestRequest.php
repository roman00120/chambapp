<?php

namespace App\Http\Requests;

use App\Services\ContactInformationGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreImmediateJobRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isClient() === true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'professional_id' => ['nullable', 'integer', 'exists:professional_profiles,id'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'min:10', 'max:1200'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'photos' => ['nullable', 'array', 'max:3'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasCoordinates = $this->filled('latitude') && $this->filled('longitude');
            if (! $hasCoordinates && (! $this->filled('address') || ! $this->filled('city') || ! $this->filled('state'))) {
                $validator->errors()->add('address', 'Comparte tu ubicación o escribe una dirección manual.');
            }
            foreach (['title', 'description', 'address'] as $field) {
                if (app(ContactInformationGuard::class)->containsRestrictedInformation($this->input($field))) {
                    $validator->errors()->add($field, ContactInformationGuard::MESSAGE);
                }
            }
        });
    }
}
