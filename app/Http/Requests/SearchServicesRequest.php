<?php

namespace App\Http\Requests;

use App\Enums\PriceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchServicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => trim((string) $this->input('q')),
            'category' => trim((string) $this->input('category')),
            'city' => trim((string) $this->input('city')),
            'state' => trim((string) $this->input('state')),
            'sort' => trim((string) $this->input('sort', 'relevant')),
        ]);
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => [
                'nullable',
                'string',
                'max:100',
                Rule::exists('categories', 'slug')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'price_type' => ['nullable', Rule::enum(PriceType::class)],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:min_price'],
            'rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'verified' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['relevant', 'rating', 'price_low', 'price_high', 'recent'])],
        ];
    }

    public function messages(): array
    {
        return [
            'q.max' => 'La búsqueda no puede superar 100 caracteres.',
            'category.exists' => 'Selecciona una categoría activa.',
            'price_type.enum' => 'Selecciona un tipo de precio válido.',
            'max_price.gte' => 'El precio máximo debe ser mayor o igual al mínimo.',
            'rating.min' => 'La calificación mínima debe ser entre 1 y 5.',
            'rating.max' => 'La calificación mínima debe ser entre 1 y 5.',
            'sort.in' => 'Selecciona un ordenamiento válido.',
        ];
    }
}
