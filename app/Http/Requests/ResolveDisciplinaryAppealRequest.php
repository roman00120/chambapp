<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolveDisciplinaryAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'accepted' => ['required', 'boolean'],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
