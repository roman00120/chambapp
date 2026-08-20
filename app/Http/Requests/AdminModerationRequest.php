<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminModerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['hide', 'restore'])],
            'reason' => ['required_if:action,hide', 'nullable', 'string', 'max:1000'],
        ];
    }
}
