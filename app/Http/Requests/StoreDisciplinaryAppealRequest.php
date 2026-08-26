<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisciplinaryAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'appeal_text' => ['required', 'string', 'min:20', 'max:3000'],
        ];
    }

    public function messages(): array
    {
        return [
            'appeal_text.required' => 'Por favor explica los motivos por los cuales consideras que la sanción debe ser revocada.',
            'appeal_text.min' => 'El texto de la apelación debe tener al menos 20 caracteres.',
        ];
    }
}
