<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'nama' => ['required_without:patient_id', 'nullable', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'min:8', 'max:32'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'usia' => ['nullable', 'integer', 'min:0', 'max:150'],
            'jenis_kelamin' => ['nullable', Rule::in(['laki-laki', 'perempuan'])],
            'alamat' => ['nullable', 'string', 'max:255'],
            'hubungan' => ['nullable', Rule::in(Patient::HUBUNGAN)],
        ];
    }
}
