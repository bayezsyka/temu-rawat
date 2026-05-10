<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor_whatsapp' => ['required', 'string', 'min:8', 'max:30'],
            'otp' => ['required', 'digits:6'],
        ];
    }
}
