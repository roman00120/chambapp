<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartIdentityVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isProfessional() === true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['identity_consent' => ['required', 'accepted']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'identity_consent.required' => 'Debes aceptar el consentimiento antes de iniciar.',
            'identity_consent.accepted' => 'Debes aceptar el consentimiento antes de iniciar.',
        ];
    }
}
