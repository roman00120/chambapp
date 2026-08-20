<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isClient() === true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in([
                'incomplete_work', 'not_as_agreed', 'damage_or_issue', 'professional_absent', 'other',
            ])],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
