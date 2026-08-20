<?php

namespace App\Http\Requests;

use App\Services\ContactInformationGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isClient() === true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (app(ContactInformationGuard::class)->containsRestrictedInformation($this->input('comment'))) {
                $validator->errors()->add('comment', ContactInformationGuard::MESSAGE);
            }
        });
    }
}
