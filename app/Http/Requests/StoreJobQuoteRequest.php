<?php

namespace App\Http\Requests;

use App\Services\ContactInformationGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreJobQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isProfessional() === true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'description' => ['required', 'string', 'max:300'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (app(ContactInformationGuard::class)->containsRestrictedInformation($this->input('description'))) {
                $validator->errors()->add('description', ContactInformationGuard::MESSAGE);
            }
        });
    }
}
