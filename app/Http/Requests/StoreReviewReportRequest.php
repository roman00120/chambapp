<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isProfessional() === true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(['offensive', 'personal_data', 'spam', 'unrelated'])],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
