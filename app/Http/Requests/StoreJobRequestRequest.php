<?php

namespace App\Http\Requests;

use App\Services\ContactInformationGuard;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreJobRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isClient() === true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:5000'],
            'requested_date' => ['required', 'date_format:Y-m-d\\TH:i', 'after_or_equal:today'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $date = $this->input('requested_date');
            if ($date) {
                try {
                    if (Carbon::createFromFormat('Y-m-d\\TH:i', $date)->isPast()) {
                        $validator->errors()->add('requested_date', 'La fecha solicitada no puede estar en el pasado.');
                    }
                } catch (\Throwable) {
                    // The date_format rule reports malformed values.
                }
            }

            foreach (['title', 'description'] as $field) {
                if (app(ContactInformationGuard::class)->containsRestrictedInformation($this->input($field))) {
                    $validator->errors()->add($field, ContactInformationGuard::MESSAGE);
                }
            }
        });
    }
}
