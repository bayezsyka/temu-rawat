<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInitialCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tekanan_darah' => ['nullable', 'string', 'max:30'],
            'berat_badan' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'tinggi_badan' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'suhu' => ['nullable', 'numeric', 'min:0', 'max:99.9'],
            'nadi' => ['nullable', 'integer', 'min:0', 'max:999'],
            'catatan_asisten' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
