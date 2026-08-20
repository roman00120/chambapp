<?php

namespace App\Http\Requests\Professional;

use App\Enums\PriceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isProfessional() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => preg_replace('/\s+/', ' ', trim((string) $this->input('title'))),
            'description' => trim((string) $this->input('description')),
        ]);
    }

    public function rules(): array
    {
        $priceRules = $this->input('price_type') === PriceType::QUOTE->value
            ? ['nullable']
            : ['required', 'numeric', 'min:0', 'max:99999999.99'];

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'description' => ['required', 'string', 'min:20', 'max:3000'],
            'price_type' => ['required', Rule::enum(PriceType::class)],
            'price' => $priceRules,
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:4096'],
            'cover_index' => ['nullable', 'integer', 'min:0', 'max:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Selecciona una categoría activa.',
            'title.required' => 'Escribe un título para tu servicio.',
            'title.min' => 'El título debe tener al menos 5 caracteres.',
            'description.required' => 'Describe detalladamente tu servicio.',
            'description.min' => 'La descripción debe tener al menos 20 caracteres.',
            'price.required_if' => 'Indica un precio para este tipo de servicio.',
            'images.max' => 'Solo puedes subir hasta 5 imágenes.',
            'images.*.mimetypes' => 'El archivo seleccionado no es una imagen válida.',
            'images.*.max' => 'La imagen supera el tamaño permitido de 4 MB.',
        ];
    }
}
