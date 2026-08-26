<?php

namespace App\Http\Requests;

use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreUserReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reported_id' => ['required', 'integer', 'exists:users,id', 'different:reporter_id'],
            'job_request_id' => ['nullable', 'integer', 'exists:job_requests,id'],
            'category' => ['required', new Enum(ReportCategory::class)],
            'severity_reported' => ['nullable', new Enum(ReportSeverity::class)],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'evidence' => ['nullable', 'array', 'max:5'],
            'evidence.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'], // 10MB max per file
            'confirm_truthfulness' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'reported_id.required' => 'Debes especificar el usuario que deseas reportar.',
            'reported_id.different' => 'No puedes reportarte a ti mismo.',
            'category.required' => 'Debes seleccionar el motivo del reporte.',
            'description.required' => 'Por favor describe lo ocurrido detalladamente.',
            'description.min' => 'La descripción debe contener al menos 10 caracteres.',
            'confirm_truthfulness.accepted' => 'Debes confirmar que la información proporcionada es verídica.',
            'evidence.*.mimes' => 'Solo se permiten imágenes (JPG, PNG, WEBP) o documentos PDF.',
            'evidence.*.max' => 'Cada archivo de evidencia no debe superar los 10MB.',
        ];
    }
}
