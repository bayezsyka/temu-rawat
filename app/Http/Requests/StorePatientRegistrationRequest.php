<?php

namespace App\Http\Requests;

use App\Models\Queue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'practice_session_id' => ['required', 'integer', 'exists:practice_sessions,id'],
            'keluhan' => ['nullable', 'string', 'max:1000'],
            'status_kunjungan' => ['required', Rule::in(Queue::STATUS_KUNJUNGAN)],
            'metode_daftar' => ['required', Rule::in(Queue::METODE_DAFTAR)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'keluhan' => filled($this->keluhan) ? trim((string) $this->keluhan) : null,
        ]);
    }
}
