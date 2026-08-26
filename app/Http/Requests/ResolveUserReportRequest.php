<?php

namespace App\Http\Requests;

use App\Enums\DisciplinaryActionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ResolveUserReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'string', Rule::in(['invalid', 'valid_yellow_card', 'valid_severe', 'close_no_action'])],
            'action_type' => ['nullable', 'required_if:decision,valid_severe', new Enum(DisciplinaryActionType::class)],
            'reason_code' => ['nullable', 'string', 'max:60'],
            'reason_text' => ['nullable', 'required_if:decision,valid_yellow_card,valid_severe', 'string', 'max:1000'],
            'suspension_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'admin_notes_private' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
