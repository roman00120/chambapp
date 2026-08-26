<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => preg_replace('/\s+/', ' ', trim((string) $this->input('name'))),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'phone' => preg_replace('/\s+/', ' ', trim((string) $this->input('phone'))),
            'role' => trim((string) $this->input('role')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'regex:/^[0-9+()\s.-]{10,20}$/'],
            'role' => ['required', Rule::in([UserRole::CLIENT->value, UserRole::PROFESSIONAL->value])],
            'password' => ['required', 'confirmed', Password::defaults()],
            'device_name' => ['required', 'string', 'max:100'],
            'legal_accepted' => ['nullable'],
            'legal_documents' => ['nullable', 'array'],
            'legal_documents.*' => ['nullable', 'string', 'max:100'],
        ];
    }
}
