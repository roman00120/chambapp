<?php

namespace App\Http\Requests;

use App\Services\ContactInformationGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RejectJobQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $quote = $this->route('quote') ?? $this->route('jobQuote');

        return $this->user()?->can('reject', $quote) === true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'in:price_high,changed_need,no_longer_needed,other'],
            'reason_detail' => ['nullable', 'string', 'max:140', 'required_if:reason,other'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (app(ContactInformationGuard::class)->containsRestrictedInformation($this->input('reason_detail'))) {
                $validator->errors()->add('reason_detail', ContactInformationGuard::MESSAGE);
            }
        });
    }
}
