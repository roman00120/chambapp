<?php

namespace App\Http\Requests;

use App\Services\ContactInformationGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreScheduledJobRequestRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:5000'],
            'scheduled_for' => ['required', 'date', 'after:now'],
            'scheduled_slot' => ['required', Rule::in(['08:00-11:00', '11:00-14:00', '14:00-17:00', '17:00-20:00'])],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach (['title', 'description', 'address'] as $field) {
                if (app(ContactInformationGuard::class)->containsRestrictedInformation($this->input($field))) {
                    $validator->errors()->add($field, ContactInformationGuard::MESSAGE);
                }
            }
        });
    }
}
