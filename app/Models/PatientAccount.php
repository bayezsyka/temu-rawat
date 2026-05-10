<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientAccount extends Model
{
    protected $fillable = [
        'nomor_whatsapp',
        'verified_at',
        'last_otp_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'last_otp_at' => 'datetime',
        ];
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function otpCodes(): HasMany
    {
        return $this->hasMany(PatientOtpCode::class);
    }
}
