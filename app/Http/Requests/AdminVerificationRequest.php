<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
