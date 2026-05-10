<?php

namespace App\Http\Requests;

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
            'nama' => ['required', 'string', 'max:255'],
            'nomor_whatsapp' => ['required', 'string', 'max:30'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'usia' => ['nullable', 'integer', 'min:0', 'max:150', 'required_without:tanggal_lahir'],
            'jenis_kelamin' => ['nullable', Rule::in(['laki-laki', 'perempuan'])],
            'alamat' => ['nullable', 'string', 'max:255'],
            'keluhan' => ['required', 'string', 'max:1000'],
            'status_kunjungan' => ['required', Rule::in(['baru', 'lama'])],
            'metode_daftar' => ['required', Rule::in(['online', 'langsung'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nomor_whatsapp' => trim((string) $this->nomor_whatsapp),
            'alamat' => $this->alamat ? trim((string) $this->alamat) : null,
            'keluhan' => trim((string) $this->keluhan),
        ]);
    }
}
