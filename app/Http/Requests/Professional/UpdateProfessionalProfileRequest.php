<?php

namespace App\Http\Requests\Professional;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfessionalProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isProfessional() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => preg_replace('/\s+/', ' ', trim((string) $this->input('name'))),
            'phone' => preg_replace('/\s+/', ' ', trim((string) $this->input('phone'))),
            'bio' => trim((string) $this->input('bio')),
            'city' => trim((string) $this->input('city')),
            'state' => trim((string) $this->input('state')),
            'postal_code' => trim((string) $this->input('postal_code')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[0-9+()\s.-]{10,20}$/'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'regex:/^[0-9A-Za-z\s-]{4,10}$/'],
            'profile_photo' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe tu nombre.',
            'name.string' => 'El nombre no es válido.',
            'name.max' => 'El nombre no puede superar los 100 caracteres.',
            'phone.required' => 'Escribe un número de teléfono.',
            'phone.regex' => 'Ingresa un número de teléfono válido.',
            'bio.max' => 'La descripción no puede superar los 2,000 caracteres.',
            'experience_years.required' => 'Indica tus años de experiencia.',
            'experience_years.integer' => 'Los años de experiencia deben ser un número entero.',
            'experience_years.min' => 'Los años de experiencia no pueden ser negativos.',
            'experience_years.max' => 'Los años de experiencia no pueden superar 60.',
            'city.max' => 'La ciudad no puede superar los 100 caracteres.',
            'state.max' => 'El estado no puede superar los 100 caracteres.',
            'postal_code.regex' => 'Ingresa un código postal válido.',
            'profile_photo.mimetypes' => 'El archivo seleccionado no es una imagen válida.',
            'profile_photo.max' => 'La imagen supera el tamaño permitido de 2 MB.',
        ];
    }
}