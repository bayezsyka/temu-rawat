<?php

namespace App\Services;

use App\Models\PatientAccount;
use App\Models\PatientOtpCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PatientOtpService
{
    public function sendOtp(string $nomorWhatsapp): PatientAccount
    {
        $normalized = $this->normalizeWhatsapp($nomorWhatsapp);

        $account = PatientAccount::query()->firstOrCreate([
            'nomor_whatsapp' => $normalized,
        ]);

        if ($account->last_otp_at && $account->last_otp_at->gt(now()->subSeconds(60))) {
            throw ValidationException::withMessages([
                'nomor_whatsapp' => 'OTP baru bisa dikirim ulang setelah 60 detik.',
            ]);
        }

        $otp = (string) random_int(100000, 999999);

        PatientOtpCode::query()->create([
            'patient_account_id' => $account->id,
            'otp_hash' => Hash::make($otp),
            'expired_at' => now()->addMinutes(5),
        ]);

        $account->forceFill([
            'last_otp_at' => now(),
        ])->save();

        $this->sendOtpMessage($normalized, $otp);

        return $account->refresh();
    }

    public function verifyOtp(string $nomorWhatsapp, string $otp): PatientAccount
    {
        $normalized = $this->normalizeWhatsapp($nomorWhatsapp);

        $account = PatientAccount::query()
            ->where('nomor_whatsapp', $normalized)
            ->first();

        if (! $account) {
            throw ValidationException::withMessages([
                'otp' => 'OTP tidak ditemukan untuk nomor ini.',
            ]);
        }

        $latestOtp = $account->otpCodes()
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $latestOtp || $latestOtp->expired_at->isPast()) {
            throw ValidationException::withMessages([
                'otp' => 'OTP sudah kedaluwarsa. Silakan kirim ulang.',
            ]);
        }

        if ($latestOtp->attempts >= 5) {
            throw ValidationException::withMessages([
                'otp' => 'Percobaan OTP sudah mencapai batas maksimal.',
            ]);
        }

        $latestOtp->increment('attempts');

        if (! Hash::check($otp, $latestOtp->otp_hash)) {
            throw ValidationException::withMessages([
                'otp' => 'Kode OTP tidak sesuai.',
            ]);
        }

        $latestOtp->forceFill([
            'verified_at' => now(),
        ])->save();

        $account->forceFill([
            'verified_at' => now(),
        ])->save();

        return $account->refresh();
    }

    public function normalizeWhatsapp(string $nomorWhatsapp): string
    {
        return preg_replace('/[^0-9]/', '', trim($nomorWhatsapp)) ?: '';
    }

    protected function sendOtpMessage(string $nomorWhatsapp, string $otp): void
    {
        Log::info('Temu Rawat OTP development message', [
            'nomor_whatsapp' => $nomorWhatsapp,
            'otp' => $otp,
        ]);
    }
}
