<?php

namespace App\Http\Requests;

use App\Services\ContactInformationGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CancelJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cancel', $this->route('jobRequest')) === true;
    }

    public function rules(): array
    {
        return ['cancellation_reason' => ['nullable', 'string', 'max:255']];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (app(ContactInformationGuard::class)->containsRestrictedInformation($this->input('cancellation_reason'))) {
                $validator->errors()->add('cancellation_reason', ContactInformationGuard::MESSAGE);
            }
        });
    }
}
