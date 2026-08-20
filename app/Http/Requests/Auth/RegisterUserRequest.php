<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
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
            'account_type' => trim((string) $this->input('account_type')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'regex:/^[0-9+()\s.-]{10,20}$/'],
            'account_type' => [
                'required',
                Rule::in([UserRole::CLIENT->value, UserRole::PROFESSIONAL->value]),
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe tu nombre.',
            'email.required' => 'Escribe tu correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'phone.required' => 'Escribe un número de teléfono.',
            'phone.regex' => 'Ingresa un número de teléfono válido.',
            'account_type.required' => 'Selecciona el tipo de cuenta.',
            'account_type.in' => 'Selecciona un tipo de cuenta válido.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'phone' => 'teléfono',
            'account_type' => 'tipo de cuenta',
            'password' => 'contraseña',
        ];
    }
}
