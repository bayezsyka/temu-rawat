<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertPracticeSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_dokter' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['buka', 'istirahat', 'selesai'])],
            'nomor_awal' => ['nullable', 'integer', 'min:1', 'max:999'],
        ];
    }
}
